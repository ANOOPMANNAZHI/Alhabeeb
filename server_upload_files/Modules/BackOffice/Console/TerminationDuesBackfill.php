<?php

namespace Modules\BackOffice\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\BackOffice\Entities\Termination;
use Modules\BackOffice\Entities\TerminationDues;
use Modules\BackOffice\Services\TerminationDuesBuilder;
use Modules\BackOffice\Services\TerminationDuesService;

/**
 * Creates dues records for contracts that were terminated before this
 * feature existed and still show an unpaid balance. Dry-run by default:
 * prints what it would create; --apply writes. Contracts whose computed
 * balance is already zero (paid, or "paid" via the old receipt-as-IOU
 * practice) are skipped and counted so the noise stays out of the list.
 */
class TerminationDuesBackfill extends Command
{
    protected $signature = 'termination-dues:backfill {--apply : Write the records (default is a dry run)} {--since= : Only terminations on/after this date (Y-m-d)} {--contract= : Single tenant_contract_no}';
    protected $description = 'Create termination dues for already-terminated contracts with an unpaid balance';

    public function handle()
    {
        $apply = (bool) $this->option('apply');
        $svc = new TerminationDuesService();

        $q = Termination::where('work_flow_processes_code', TerminationDuesService::FINAL_STAGE)
            ->whereIn('contract_id', function ($s) {
                $s->select('id')->from('tenant_contracts')->where('tenant_renewal_termination_status', 8);
            })
            ->whereNotIn('contract_id', function ($s) { $s->select('tenant_contract_id')->from('termination_dues'); })
            ->orderBy('contract_id')->orderBy('id');
        if ($this->option('since')) {
            $q->whereDate('termination_date', '>=', $this->option('since'));
        }
        if ($this->option('contract')) {
            $q->whereIn('contract_id', function ($s) { $s->select('id')->from('tenant_contracts')->where('tenant_contract_no', $this->option('contract')); });
        }

        $seen = [];
        $created = 0; $skippedZero = 0; $skippedNothing = 0; $failed = 0;
        $rows = [];
        foreach ($q->get() as $final) {
            if (isset($seen[$final->contract_id])) { continue; }
            $seen[$final->contract_id] = true;
            try {
                $lines = TerminationDuesBuilder::build($svc->buildInputFor($final->contract_id, $final));
                if (empty($lines)) { $skippedNothing++; continue; }

                DB::beginTransaction();
                $dues = $svc->createForTermination($final, null);
                if (!$dues) { DB::rollBack(); $skippedNothing++; continue; }
                $r = $svc->refresh($dues);
                if ($r['total']['balance'] <= 0.005) {
                    DB::rollBack(); $skippedZero++; continue;
                }
                $rows[] = [$final->contract_id, DB::table('tenant_contracts')->where('id', $final->contract_id)->value('tenant_contract_no'), (string) $final->termination_date, number_format($r['total']['owed'], 3), number_format($r['total']['balance'], 3), $r['status']];
                if ($apply) { DB::commit(); $created++; } else { DB::rollBack(); $created++; }
            } catch (\Exception $e) {
                DB::rollBack();
                $failed++;
                $this->warn('contract ' . $final->contract_id . ': ' . $e->getMessage());
            }
        }

        $this->table(['contract_id', 'contract_no', 'termination_date', 'owed', 'balance', 'status'], array_slice($rows, 0, 200));
        if (count($rows) > 200) { $this->line('... ' . (count($rows) - 200) . ' more'); }
        $this->info(($apply ? 'Created ' : 'Would create ') . $created . ' dues record(s); skipped ' . $skippedZero . ' already settled, ' . $skippedNothing . ' with nothing owed; ' . $failed . ' failed.');
        if (!$apply) { $this->comment('Dry run. Re-run with --apply to write.'); }
        return 0;
    }
}
