<?php

namespace Modules\BackOffice\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\BackOffice\Entities\TerminationDues;
use Modules\BackOffice\Services\RentLedger;
use Modules\BackOffice\Services\RentOutstanding;
use Modules\BackOffice\Services\TerminationDuesCategory as Cat;
use Modules\BackOffice\Services\TerminationDuesService;

/**
 * One-off after option A (user, 03/10/2026): early terminations dated from
 * 01/08/2026 are charged by the day with 30-day months. The figures stored when
 * those contracts were terminated were worked out with the old rule (a started
 * month = a whole month), or are wrong, so both are recalculated fresh:
 *   - tenant_contracts.tenant_contract_os = new rent due - counted rent receipts
 *     (unfloored: negative = overpaid, as the termination screens store it)
 *   - the computed "Outstanding rent" line of the termination dues = new rent
 *     due - counted receipts up to the dues cut-off (never below 0)
 * Only contracts whose rent due changes under the new rule are touched (a
 * termination on a whole month is left alone), and only when their stored
 * figures differ; re-running changes nothing.
 * Dry run by default; --apply writes, one contract per transaction, each change
 * logged in rent_outstanding_day_count_corrections.
 * Runs on PHP < 7.4 in production: no arrow functions.
 */
class RentOutstandingApplyDayCount extends Command
{
    protected $signature = 'rent-outstanding:apply-day-count {--apply : Write the changes (default is a dry run)} {--contract= : Single tenant_contract_no}';
    protected $description = 'Recalculate stored rent outstanding on early terminations from 01/08/2026 with the 30-day rule';

    public function handle()
    {
        $apply = (bool) $this->option('apply');
        $svc = new TerminationDuesService();

        // latest termination row (with a date) per contract, early terminations from the cut-over
        $latest = DB::table('termination')
            ->whereNotNull('termination_date')
            ->groupBy('contract_id')
            ->select('contract_id', DB::raw('MAX(id) AS id'));
        $q = DB::table('tenant_contracts as c')
            ->joinSub($latest, 'lt', function ($j) { $j->on('lt.contract_id', '=', 'c.id'); })
            ->join('termination as t', 't.id', '=', 'lt.id')
            ->where('t.termination_date', '>=', RentLedger::DAY_COUNT_FROM)
            ->whereRaw('t.termination_date::date < c.tenant_contract_valid_to_date::date')
            ->select('c.id', 'c.tenant_contract_no', 'c.tenant_contract_effective_date', 'c.tenant_contract_start_date',
                     'c.tenant_contract_rent', 'c.tenant_contract_value', 'c.tenant_contract_valid_to_date',
                     'c.tenant_contract_os', 't.termination_date')
            ->orderBy('c.id');
        if ($this->option('contract')) {
            $q->where('c.tenant_contract_no', $this->option('contract'));
        }

        $rows = [];
        $changed = 0; $failed = 0; $checked = 0;
        foreach ($q->get() as $c) {
            $checked++;
            $from = $c->tenant_contract_effective_date ?: $c->tenant_contract_start_date;
            $args = [$from, $c->termination_date, $c->tenant_contract_rent, 0, $c->tenant_contract_value, $c->tenant_contract_valid_to_date];
            $dueOld = call_user_func_array(RentOutstanding::compute(...), $args)['rent_due'];
            $dueNew = call_user_func_array(RentOutstanding::compute(...), array_merge($args, [true]))['rent_due'];
            if (abs($dueOld - $dueNew) < 0.0005) {
                continue; // the new rule does not change this contract: leave its stored figures alone
            }

            $osOld = $c->tenant_contract_os;
            $osNew = round($dueNew - RentOutstanding::paidForContract($c->id), 3);

            $dues = TerminationDues::where('tenant_contract_id', $c->id)->first();
            $line = $dues ? $dues->lines()->where('category', Cat::RENT)->where('source_type', 'computed')->first() : null;
            $lineOld = $line ? $line->amount : null;
            $lineNew = $line ? max(round($dueNew - $this->paidUpTo($c->id, $dues->rent_receipts_upto_id), 3), 0.0) : null;

            $osChanges   = $osOld === null || abs((float) $osOld - $osNew) > 0.0005;
            $lineChanges = $line !== null && abs((float) $lineOld - $lineNew) > 0.0005;
            if (!$osChanges && !$lineChanges) {
                continue; // already right
            }

            $rows[] = [$c->tenant_contract_no, date('Y-m-d', strtotime($c->termination_date)),
                       number_format($dueOld, 3), number_format($dueNew, 3),
                       $osOld === null ? '-' : number_format((float) $osOld, 3), number_format($osNew, 3),
                       $lineOld === null ? '-' : number_format((float) $lineOld, 3), $lineNew === null ? '-' : number_format($lineNew, 3)];

            if (!$apply) {
                $changed++;
                continue;
            }
            try {
                DB::transaction(function () use ($c, $dueOld, $dueNew, $osChanges, $osOld, $osNew, $lineChanges, $line, $lineOld, $lineNew, $dues, $svc) {
                    DB::table('rent_outstanding_day_count_corrections')->insert([
                        'tenant_contract_id' => $c->id,
                        'termination_date'   => date('Y-m-d', strtotime($c->termination_date)),
                        'due_old'            => $dueOld,
                        'due_new'            => $dueNew,
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
                    }
                    if ($dues) {
                        $svc->refresh($dues->fresh());
                    }
                });
                Log::info('rent-outstanding day-count', ['contract' => $c->tenant_contract_no, 'due' => [$dueOld, $dueNew], 'os' => [$osOld, $osNew], 'dues_rent_line' => [$lineOld, $lineNew]]);
                $changed++;
            } catch (\Exception $e) {
                $failed++;
                $this->warn($c->tenant_contract_no . ': ' . $e->getMessage());
            }
        }

        $this->table(['contract_no', 'termination_date', 'due_old', 'due_new', 'os_old', 'os_new', 'dues_rent_old', 'dues_rent_new'], $rows);
        $this->info(($apply ? 'Changed ' : 'Would change ') . $changed . ' contract(s) of ' . $checked . ' early termination(s) from ' . RentLedger::DAY_COUNT_FROM . '; ' . $failed . ' failed.');
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
