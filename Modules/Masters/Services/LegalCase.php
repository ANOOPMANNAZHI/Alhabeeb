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
        'dropped' => 'Dropped after refer back',
        'closed' => 'Closed',
        'before_upgrade' => 'Closed (before upgrade)',
    ];

    const KINDS = [
        'moved' => 'Moved to legal',
        'plm_approved' => 'PLM approved',
        'plm_referback' => 'PLM referred back',
        'are_resent' => 'Sent to PLM again by ARE',
        'lawyer_approved' => 'Lawyer approved',
        'lawyer_rejected' => 'Lawyer rejected',
        'handled_by' => 'Handled by changed',
        'handed_over' => 'Handed over',
        'stage' => 'Stage updated',
        'comment' => 'Comment',
        'reassigned' => 'Lawyer changed',
        'lawyer_assigned' => 'Lawyer assigned',
        'closed' => 'Closed',
        'reopened' => 'Reopened',
        'type_changed' => 'Case type changed',
        'settlement' => 'Settlement',
        'settlement_verified' => 'Settlement verified',
        'settlement_sent_back' => 'Settlement sent back',
        'settlement_broken' => 'Settlement broken',
    ];

    const APPLIES_TO = [0 => 'Both types', 1 => 'Occupying only', 2 => 'Vacated only'];

    /** Default "handled by" for a building, from its ownership type. */
    public static function defaultHandledBy($buildingId)
    {
        return self::handledByForOwnership(DB::table('buildings')->where('id', $buildingId)->value('building_ownership_type'));
    }

    /** Handled by that a building ownership type suggests: Government / MERA building, else Al Habib. */
    public static function handledByForOwnership($ownership)
    {
        $ownership = (int) $ownership;
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

    /**
     * What the contract owes (user, 18/10/2026): rent outstanding from receipts and waivers, and for a terminated
     * contract the termination dues balance, which already holds the rent plus E&W and checklist charges.
     * total = the dues balance when there are dues, else the rent outstanding (never both added).
     * @return array rent, dues (TerminationDuesService::summaryForContract or null), total
     */
    public static function owed(Legal $legal)
    {
        $rent = round((float) self::outstanding($legal), 3);
        $dues = null;
        try {
            $dues = (new \Modules\BackOffice\Services\TerminationDuesService)->summaryForContract($legal->tenant_contract_id);
        } catch (\Exception $e) {
            \Log::warning('Legal owed: termination dues of contract ' . $legal->tenant_contract_id . ' failed: ' . $e->getMessage());
        }
        return ['rent' => $rent, 'dues' => $dues, 'total' => $dues ? round((float) $dues['balance'], 3) : $rent];
    }

    /** Stage master rows a case can be put at: active, for its type, not the final "Closed" (closing has its own rules). */
    public static function stagesFor(Legal $legal)
    {
        // the current stage stays listed even when it no longer applies (eviction turns a case into "vacated")
        return DB::table('legal_stages')->where('is_final', false)
            ->where(function ($q) use ($legal) {
                $q->where(function ($w) use ($legal) {
                    $w->where('status', 1)->whereIn('applies_to', [0, (int) $legal->case_type]);
                })->orWhere('id', (int) $legal->stage_id);
            })
            ->orderBy('sort_order')->orderBy('id')->get();
    }

    public static function stage($id)
    {
        return $id ? DB::table('legal_stages')->where('id', $id)->first() : null;
    }

    /** Active case worked by our lawyer: approved by the lawyer (803) and still open. */
    public static function isActive(Legal $legal)
    {
        return !$legal->closed_at && (int) $legal->work_flow_processes_code === 803
            && (int) ($legal->handled_by ?: self::HANDLED_ALHABIB) === self::HANDLED_ALHABIB;
    }

    /** Why the case cannot go to this stage, or null. A stage can repeat (another hearing) but not go back. */
    public static function stageError(Legal $legal, $stage)
    {
        if (!self::isActive($legal)) {
            return 'Stages can be updated only on an active case (approved by the lawyer and not closed).';
        }
        if (!$stage || !(int) $stage->status || $stage->is_final) {
            return 'Pick a valid stage.';
        }
        if (!in_array((int) $stage->applies_to, [0, (int) $legal->case_type], true)) {
            return 'Stage "' . $stage->name . '" does not apply to ' . self::TYPES[(int) $legal->case_type] . ' cases.';
        }
        $current = self::stage($legal->stage_id);
        if ($current && (int) $stage->sort_order < (int) $current->sort_order && !self::brokenSinceLastStage($legal)) {
            return 'The case is at "' . $current->name . '"; it cannot move back to "' . $stage->name . '".';
        }
        return null;
    }

    /**
     * The case page (stages, settlement, comments) exists once the lawyer accepted the case (803); before that
     * the case is only a request at PLM / lawyer approval, and a case handed over at PLM approval never gets one.
     */
    public static function hasCasePage(Legal $legal)
    {
        return (int) $legal->work_flow_processes_code === 803;
    }

    /** Where a case opens: the case page once accepted, otherwise the screen of the step it is at. */
    public static function screenUrl(Legal $legal)
    {
        $code = (int) $legal->work_flow_processes_code;
        if ($code === 803) {
            return route('legal.case', $legal->id);
        }
        if ($code === 801) {
            return route('plmsApprovalShow', $legal->id);
        }
        if ($code === 802) {
            return route('lawyerApprovalShow', $legal->id);
        }
        return route('legalCase.show', $legal->id);
    }

    /** A broken settlement lets the lawyer resume the case at any stage (once, until the next stage update). */
    public static function brokenSinceLastStage(Legal $legal)
    {
        $settlement = LegalSettlement::current($legal->id);
        if (!$settlement || (int) $settlement->status !== LegalSettlement::BROKEN || !$settlement->broken_at) {
            return false;
        }
        $lastStage = DB::table('legal_case_updates')->where('legal_id', $legal->id)->where('kind', 'stage')->max('created_at');
        return !$lastStage || $lastStage < $settlement->broken_at;
    }

    /** Stage updates: the assigned lawyer (or anyone with the permission who is not only a lawyer). */
    public static function canUpdateStage($user, Legal $legal)
    {
        if (!$user->can('legal_stage_update')) {
            return false;
        }
        if ($user->hasRole(['super_admin', 'md', 'backoffice_manager'])) {
            return true;
        }
        return !$legal->lawyer_user_id || (int) $legal->lawyer_user_id === (int) $user->id;
    }

    public static function canView($user)
    {
        foreach (['active_cases_view', 'plms_approval_view', 'lawyer_approval_view', 'closed_legal_cases_view', 'handed_over_cases_list', 'legal_case_view'] as $p) {
            if ($user->can($p)) {
                return true;
            }
        }
        return false;
    }

    /** Store uploaded files as legal_documents of a timeline row. */
    public static function storeFiles($legalId, $updateId, $files, $userId)
    {
        $now = now();
        foreach ((array) $files as $file) {
            if (!$file) {
                continue;
            }
            $path = \Illuminate\Support\Facades\Storage::putFile('public/LegalDocs', $file);
            DB::table('legal_documents')->insert(['legal_id' => $legalId, 'update_id' => $updateId,
                'legal_documents_name' => mb_substr($file->getClientOriginalName(), 0, 250), 'legal_documents_file_name' => $path,
                'created_by' => $userId, 'created_at' => $now, 'updated_at' => $now]);
        }
    }

    /** Files of the case grouped by timeline row (key 0 = files with no timeline row). */
    public static function documentsByUpdate($legalId)
    {
        return DB::table('legal_documents')->where('legal_id', $legalId)->orderBy('id')->get()
            ->groupBy(function ($d) { return (int) $d->update_id; });
    }

    /**
     * Lawyer dashboard: their active cases by stage and hearings in the next 14 days.
     * $userId null = every lawyer (admins / PLM).
     */
    public static function lawyerSummary($userId = null)
    {
        $base = DB::table('legal as l')->whereNull('l.closed_at')->where('l.work_flow_processes_code', 803)
            ->where('l.legal_is_closed', 2)
            ->when($userId, function ($q) use ($userId) { $q->where('l.lawyer_user_id', $userId); });

        $byStage = (clone $base)->leftJoin('legal_stages as s', 's.id', '=', 'l.stage_id')
            ->groupBy('s.id', 's.name', 's.sort_order')->orderByRaw('s.sort_order nulls first')
            ->select('s.id', DB::raw("coalesce(s.name, 'Stage not set') as name"), DB::raw('count(*) as n'))->get();

        $hearings = (clone $base)->whereBetween('l.next_hearing_date', [date('Y-m-d'), date('Y-m-d', strtotime('+14 days'))])
            ->join('tenant_contracts as tc', 'tc.id', '=', 'l.tenant_contract_id')
            ->leftJoin('tenant as t', 't.id', '=', 'tc.tenant_id')
            ->leftJoin('buildings as b', 'b.id', '=', 'l.building_id')
            ->select('l.id', 'l.next_hearing_date', 'l.court_case_no', 'l.court_name', 'tc.tenant_contract_no', 't.tenant_name', 'b.building_name')
            ->orderBy('l.next_hearing_date')->limit(20)->get();

        return ['total' => (int) $byStage->sum('n'), 'byStage' => $byStage, 'hearings' => $hearings];
    }
}
