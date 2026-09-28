<?php

namespace Modules\BackOffice\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\BackOffice\Entities\TerminationDues;
use Modules\BackOffice\Services\RentOutstanding;
use Modules\BackOffice\Services\TerminationDuesCategory as Cat;
use Modules\BackOffice\Services\TerminationDuesService;

/**
 * One-off correction after the rent-outstanding fix (short / part-month
 * contracts capped at their contract value). Terminations processed before the
 * fix stored the old, higher figure in two places:
 *   - tenant_contracts.tenant_contract_os
 *   - the computed "Outstanding rent" line of the contract's termination dues
 * Only the part above the new rule is removed (RentOutstanding::correctedStoredAmount),
 * so re-running, or running on figures stored after the fix, changes nothing.
 * Dry run by default; --apply writes, one contract per transaction.
 * Runs on PHP < 7.4 in production: no arrow functions.
 */
class RentOutstandingCorrectShortContracts extends Command
{
    protected $signature = 'rent-outstanding:correct-short-contracts {--apply : Write the corrections (default is a dry run)} {--contract= : Single tenant_contract_no}';
    protected $description = 'Correct stored rent outstanding on terminated short / part-month contracts after the contract-value cap fix';

    public function handle()
    {
        $apply = (bool) $this->option('apply');
        $svc = new TerminationDuesService();

        // latest termination row (with a date) per contract
        $latest = DB::table('termination')
            ->whereNotNull('termination_date')
            ->groupBy('contract_id')
            ->select('contract_id', DB::raw('MAX(id) AS id'));
        $q = DB::table('tenant_contracts as c')
            ->joinSub($latest, 'lt', function ($j) { $j->on('lt.contract_id', '=', 'c.id'); })
            ->join('termination as t', 't.id', '=', 'lt.id')
            ->select('c.id', 'c.tenant_contract_no', 'c.tenant_contract_effective_date', 'c.tenant_contract_start_date',
                     'c.tenant_contract_rent', 'c.tenant_contract_value', 'c.tenant_contract_valid_to_date',
                     'c.tenant_contract_os', 't.termination_date')
            ->orderBy('c.id');
        if ($this->option('contract')) {
            $q->where('c.tenant_contract_no', $this->option('contract'));
        }
        // Each contract is corrected once: stored figures from older formulas cannot be
        // recognised as already corrected, so the audit table is the source of truth.
        $q->whereNotIn('c.id', function ($s) { $s->select('tenant_contract_id')->from('rent_outstanding_corrections'); });

        $rows = [];
        $changed = 0; $failed = 0; $checked = 0;
        foreach ($q->get() as $c) {
            $checked++;
            $from = $c->tenant_contract_effective_date ?: $c->tenant_contract_start_date;
            $reduction = RentOutstanding::capReduction($from, $c->termination_date, $c->tenant_contract_rent,
                $c->tenant_contract_value, $c->tenant_contract_valid_to_date);
            if ($reduction <= 0) {
                continue;
            }
            $newDue = round(RentOutstanding::rentDue($from, $c->termination_date, $c->tenant_contract_rent) - $reduction, 3);

            $osOld = $c->tenant_contract_os;
            // stored unfloored by the termination screens: negative = overpaid, keep it that way
            $osNew = RentOutstanding::correctedStoredAmount($osOld, RentOutstanding::paidForContract($c->id), $newDue, $reduction, false);

            $dues = TerminationDues::where('tenant_contract_id', $c->id)->first();
            $line = $dues ? $dues->lines()->where('category', Cat::RENT)->where('source_type', 'computed')->first() : null;
            $lineOld = $line ? $line->amount : null;
            $lineNew = $line ? RentOutstanding::correctedStoredAmount($lineOld, $this->paidUpTo($c->id, $dues->rent_receipts_upto_id), $newDue, $reduction) : null;

            $osChanges   = $osNew !== null && abs((float) $osOld - $osNew) > 0.0005;
            $lineChanges = $lineNew !== null && abs((float) $lineOld - $lineNew) > 0.0005;
            if (!$osChanges && !$lineChanges) {
                continue; // already correct
            }

            $rows[] = [$c->tenant_contract_no, date('Y-m-d', strtotime($c->termination_date)),
                       number_format($reduction, 3),
                       $osOld === null ? '-' : number_format((float) $osOld, 3), $osNew === null ? '-' : number_format($osNew, 3),
                       $lineOld === null ? '-' : number_format((float) $lineOld, 3), $lineNew === null ? '-' : number_format($lineNew, 3)];

            if (!$apply) {
                $changed++;
                continue;
            }
            try {
                DB::transaction(function () use ($c, $osChanges, $osOld, $osNew, $lineChanges, $line, $lineOld, $lineNew, $dues, $svc, $reduction) {
                    DB::table('rent_outstanding_corrections')->insert([
                        'tenant_contract_id' => $c->id,
                        'termination_date'   => date('Y-m-d', strtotime($c->termination_date)),
                        'rule_reduction'     => $reduction,
                        'os_old'             => $osChanges ? $osOld : null,
                        'os_new'             => $osChanges ? $osNew : null,
                        'dues_line_id'       => $lineChanges ? $line->id : null,
                        'dues_line_old'      => $lineChanges ? $lineOld : null,
                        'dues_line_new'      => $lineChanges ? $lineNew : null,
                        'created_at'         => now(),
                    ]);
                    if ($osChanges) {
                        DB::table('tenant_contracts')->where('id', $c->id)->update(['tenant_contract_os' => $osNew]);
                    }
                    if ($lineChanges) {
                        $line->amount = $lineNew;
                        $line->save();
                        $svc->refresh($dues->fresh());
                    }
                });
                Log::info('rent-outstanding correction', ['contract' => $c->tenant_contract_no, 'os' => [$osOld, $osNew], 'dues_rent_line' => [$lineOld, $lineNew]]);
                $changed++;
            } catch (\Exception $e) {
                $failed++;
                $this->warn($c->tenant_contract_no . ': ' . $e->getMessage());
            }
        }

        $this->table(['contract_no', 'termination_date', 'rule_reduction', 'os_old', 'os_new', 'dues_rent_old', 'dues_rent_new'], $rows);
        $this->info(($apply ? 'Corrected ' : 'Would correct ') . $changed . ' contract(s) of ' . $checked . ' with a termination; ' . $failed . ' failed.');
        if (!$apply) {
            $this->comment('Dry run. Re-run with --apply to write.');
        }
        return 0;
    }

    /** Counted rent receipts already netted into the dues rent line (id <= the dues cut-off). */
    private function paidUpTo($contractId, $uptoId)
    {
        if (!$uptoId) {
            return 0.0;
        }
        return (float) DB::table('receipts_generation')
            ->where('tenant_contract_id', $contractId)
            ->where('receipts_generation_type', 0)
            ->whereIn('receipts_generation_approval_status', RentOutstanding::COUNTED_APPROVAL)
            ->where('receipts_generation_status', '<>', RentOutstanding::STATUS_CANCELLED)
            ->whereNull('deleted_at')
            ->where('id', '<=', (int) $uptoId)
            ->sum('receipts_generation_amt');
    }
}
