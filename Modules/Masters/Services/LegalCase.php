<?php

namespace Modules\Masters\Services;

use Illuminate\Support\Facades\DB;
use Modules\Masters\Entities\Legal;

/**
 * Shared rules for legal cases (plan 2026-10-13, phase 1).
 *
 * A case is open while closed_at is null. Who handles it defaults from the building's ownership type
 * (buildings.building_ownership_type 1 MERA, 2 Government, 3 Family Property, 4 Individual):
 * Government and MERA cases go to their own legal department, the rest to the Al Habib lawyer.
 * Cases not handled by Al Habib are handed over and closed by PLM; the contract stays in legal
 * (tenant_contracts.status 5) so it keeps out of the normal receivables.
 */
class LegalCase
{
    const HANDLED_ALHABIB = 1;
    const HANDLED_LANDLORD = 2;
    const HANDLED_GOVERNMENT = 3;
    const HANDLED_MERA = 4;

    const HANDLED_BY = [1 => 'Al Habib', 2 => 'Landlord', 3 => 'Government', 4 => 'MERA'];
    const TYPES = [1 => 'Occupying / refusing', 2 => 'Vacated / terminated'];
    const PRIORITIES = [1 => 'High', 2 => 'Medium', 3 => 'Low'];

    const CLOSE_REASONS = [
        'handed_over' => 'Handed over',
        'paid_in_full' => 'Settled - paid in full',
        'written_off' => 'Settled - balance written off',
        'withdrawn' => 'Withdrawn',
        'closed' => 'Closed',
        'before_upgrade' => 'Closed (before upgrade)',
    ];

    const KINDS = [
        'moved' => 'Moved to legal',
        'plm_approved' => 'PLM approved',
        'plm_referback' => 'PLM referred back',
        'lawyer_approved' => 'Lawyer approved',
        'lawyer_rejected' => 'Lawyer rejected',
        'handled_by' => 'Handled by changed',
        'handed_over' => 'Handed over',
        'stage' => 'Stage updated',
        'comment' => 'Comment',
        'reassigned' => 'Lawyer changed',
        'closed' => 'Closed',
        'reopened' => 'Reopened',
    ];

    /** Default "handled by" for a building, from its ownership type. */
    public static function defaultHandledBy($buildingId)
    {
        $ownership = (int) DB::table('buildings')->where('id', $buildingId)->value('building_ownership_type');
        if ($ownership === 2) {
            return self::HANDLED_GOVERNMENT;
        }
        if ($ownership === 1) {
            return self::HANDLED_MERA;
        }
        return self::HANDLED_ALHABIB;
    }

    /** The open case of a contract, or null. */
    public static function openCaseFor($contractId)
    {
        return Legal::where('tenant_contract_id', $contractId)->whereNull('closed_at')->orderBy('id', 'desc')->first();
    }

    /**
     * Open a case for a contract at PLM approval (801). Returns [Legal, created?]; when the contract
     * already has an open case nothing is created and that case is returned.
     */
    public static function open($contract, $note, $userId)
    {
        return DB::transaction(function () use ($contract, $note, $userId) {
            // lock the contract row so two clicks cannot both create a case
            DB::table('tenant_contracts')->where('id', $contract->id)->lockForUpdate()->first();
            $existing = self::openCaseFor($contract->id);
            if ($existing) {
                return [$existing, false];
            }
            $previous = Legal::where('tenant_contract_id', $contract->id)->orderBy('id', 'desc')->first();

            $legal = Legal::create([
                'building_id' => $contract->building_id,
                'unit_id' => $contract->unit_id,
                'tenant_id' => $contract->tenant_id,
                'tenant_contract_id' => $contract->id,
                'note' => $note,
                'work_flow_processes_code' => 801,
                'handled_by' => self::defaultHandledBy($contract->building_id),
                'case_type' => (int) $contract->tenant_contract_status === 1 ? 1 : 2,
                'priority' => $previous ? $previous->priority : null,
                'lawyer_user_id' => $previous ? $previous->lawyer_user_id : null,
                'parent_legal_id' => $previous ? $previous->id : null,
                'created_by' => $userId,
            ]);
            self::log($legal->id, 'moved', $note, $userId);
            if ($previous) {
                self::log($legal->id, 'reopened', 'Reopened from case #' . $previous->id . '.', $userId);
            }
            return [$legal, true];
        });
    }

    public static function log($legalId, $kind, $note, $userId, array $extra = [])
    {
        $now = now();
        return DB::table('legal_case_updates')->insertGetId($extra + [
            'legal_id' => $legalId, 'kind' => $kind, 'note' => $note,
            'created_by' => $userId, 'created_at' => $now, 'updated_at' => $now,
        ]);
    }

    /** Close a case (closed_at + reason) and log it. Contract status is the caller's business. */
    public static function close(Legal $legal, $reason, $note, $userId)
    {
        $legal->update(['closed_at' => now(), 'closed_by' => $userId, 'close_reason' => $reason]);
        self::log($legal->id, 'closed', trim(self::reasonLabel($reason) . ($note ? ': ' . $note : '')), $userId);
    }

    public static function reasonLabel($reason)
    {
        return isset(self::CLOSE_REASONS[$reason]) ? self::CLOSE_REASONS[$reason] : (string) $reason;
    }

    /** Active logins holding the legal_advisor role, id => name. */
    public static function lawyers()
    {
        return DB::table('users as u')
            ->join('model_has_roles as m', 'm.model_id', '=', 'u.id')
            ->join('roles as r', 'r.id', '=', 'm.role_id')
            ->leftJoin('employees as e', function ($j) {
                $j->on('e.id', '=', 'u.user_type_id')->where('u.user_type', '=', 'employee');
            })
            ->where('r.name', 'legal_advisor')->where('u.user_type_status', true)
            ->where(function ($q) {
                $q->where('u.user_type', '<>', 'employee')->orWhere('e.employee_status', 1);
            })
            ->select('u.id', DB::raw('coalesce(e.employee_name, u.username) as name'))
            ->orderBy('name')->pluck('name', 'id')->all();
    }

    /** Timeline rows, newest first, with the user's name. */
    public static function timeline($legalId)
    {
        return DB::table('legal_case_updates as c')
            ->leftJoin('users as u', 'u.id', '=', 'c.created_by')
            ->leftJoin('legal_stages as s', 's.id', '=', 'c.stage_id')
            ->where('c.legal_id', $legalId)
            ->select('c.*', 'u.username', 's.name as stage_name')
            ->orderBy('c.id', 'desc')->get();
    }

    /** System rent outstanding today for the case's contract. */
    public static function outstanding(Legal $legal)
    {
        return \Modules\BackOffice\Services\RentOutstanding::amountForContract($legal->tenant_contract_id);
    }
}
