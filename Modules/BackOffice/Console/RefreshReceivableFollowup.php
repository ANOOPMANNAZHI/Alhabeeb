<?php

namespace Modules\BackOffice\Console;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Modules\BackOffice\Services\ReceivableFigures;

/**
 * Keeps the Receivables Follow-up figures warm for the cutoffs of last month and
 * the current month (each = end of the month two months before).
 */
class RefreshReceivableFollowup extends Command
{
    protected $signature = 'receivables:refresh-followup';
    protected $description = 'Refresh the cached figures of the Receivables Follow-up screen';

    public function handle()
    {
        $figures = app(ReceivableFigures::class);
        // cutoffs of the default month (last month) and the current month, e.g. Oct -> 31/07 and 31/08
        list($m, $y) = ReceivableFigures::lastMonth();
        $today = Carbon::today();
        $dates = [ReceivableFigures::asOnDate($m, $y), ReceivableFigures::asOnDate((int) $today->month, (int) $today->year)];
        foreach (array_unique($dates) as $asOn) {
            try {
                $data = $figures->refresh($asOn);
                $this->info($asOn . ': ' . count($data['rows']) . ' contracts');
            } catch (\Exception $e) {
                Log::error('receivables:refresh-followup ' . $asOn . ' failed - ' . $e->getMessage());
                $this->error($asOn . ' failed: ' . $e->getMessage());
            }
        }
        return 0;
    }
}
