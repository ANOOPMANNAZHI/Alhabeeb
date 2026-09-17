<?php

namespace Modules\BackOffice\Console;

use Illuminate\Console\Command;
use Modules\BackOffice\Entities\TerminationDues;
use Modules\BackOffice\Services\TerminationDuesService;

/** Recomputes stored balances/status for every dues record (nightly safety net for the hooks). */
class TerminationDuesRefresh extends Command
{
    protected $signature = 'termination-dues:refresh {--open-only : Only records that still have a balance}';
    protected $description = 'Recompute termination dues balances from live receipts and deposit refunds';

    public function handle()
    {
        $svc = new TerminationDuesService();
        $q = TerminationDues::orderBy('id');
        if ($this->option('open-only')) { $q->where('balance', '>', 0); }
        $n = 0;
        $q->chunk(200, function ($chunk) use ($svc, &$n) {
            foreach ($chunk as $dues) { $svc->refresh($dues); $n++; }
        });
        $this->info('Refreshed ' . $n . ' dues record(s).');
        return 0;
    }
}
