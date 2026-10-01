<?php

namespace Modules\BackOffice\Console;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Modules\BackOffice\Services\ReceivableFigures;

/**
 * Keeps the Receivables Follow-up figures warm: last month's as-on date always,
 * and today's (the current month) when someone opened it today.
 */
class RefreshReceivableFollowup extends Command
{
    protected $signature = 'receivables:refresh-followup';
    protected $description = 'Refresh the cached figures of the Receivables Follow-up screen';

    public function handle()
    {
        $figures = app(ReceivableFigures::class);
        list($m, $y) = ReceivableFigures::lastMonth();
        $dates = [ReceivableFigures::asOnDate($m, $y)];
        $today = Carbon::today()->format('Y-m-d');
        if (Cache::has(ReceivableFigures::key($today))) {
            $dates[] = $today;
        }
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
