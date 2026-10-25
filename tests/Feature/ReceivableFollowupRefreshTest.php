<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Support\Facades\Cache;
use Modules\BackOffice\Services\ReceivableFigures;

class FakeFigures extends ReceivableFigures
{
    public static $computed = [];
    public static $fail = false;
    public function compute($asOn)
    {
        if (self::$fail) {
            throw new \RuntimeException('db down');
        }
        self::$computed[] = $asOn;
        return [5 => ['due' => 10.0, 'received' => 0.0, 'balance' => 10.0, 'last_received' => null]];
    }
}

class ReceivableFollowupRefreshTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        FakeFigures::$computed = [];
        FakeFigures::$fail = false;
        $this->app->bind(ReceivableFigures::class, FakeFigures::class);
    }

    private function lastMonthAsOn()
    {
        list($m, $y) = ReceivableFigures::lastMonth();
        return ReceivableFigures::asOnDate($m, $y);
    }

    public function test_refreshes_last_month()
    {
        $this->artisan('receivables:refresh-followup')->assertExitCode(0);
        $this->assertStringContainsString($this->lastMonthAsOn(), FakeFigures::$computed);
        $this->assertSame(10.0, Cache::get(ReceivableFigures::key($this->lastMonthAsOn()))['rows'][5]['balance']);
    }

    public function test_also_refreshes_the_current_month_cutoff()
    {
        $current = ReceivableFigures::asOnDate((int) date('n'), (int) date('Y'));   // e.g. October -> 31 August
        $this->artisan('receivables:refresh-followup')->assertExitCode(0);
        $this->assertStringContainsString($current, FakeFigures::$computed);
    }

    public function test_failure_keeps_previous_figures()
    {
        $key = ReceivableFigures::key($this->lastMonthAsOn());
        Cache::put($key, ['computed_at' => 'old', 'rows' => []], 10);
        FakeFigures::$fail = true;
        $this->artisan('receivables:refresh-followup');
        $this->assertSame('old', Cache::get($key)['computed_at']);
    }

    public function test_only_one_calculation_per_month_at_a_time()
    {
        $asOn = $this->lastMonthAsOn();
        Cache::put(ReceivableFigures::key($asOn) . ':busy', 1, 2);
        try {
            app(ReceivableFigures::class)->get($asOn);
            $this->fail('expected FiguresBusy');
        } catch (\Modules\BackOffice\Services\FiguresBusy $e) {
            $this->assertSame([], FakeFigures::$computed);
        }
    }

    public function test_busy_marker_released_after_success_and_failure()
    {
        $asOn = $this->lastMonthAsOn();
        app(ReceivableFigures::class)->refresh($asOn);
        $this->assertFalse(Cache::has(ReceivableFigures::key($asOn) . ':busy'));
        FakeFigures::$fail = true;
        try {
            app(ReceivableFigures::class)->refresh($asOn);
        } catch (\RuntimeException $e) {
        }
        $this->assertFalse(Cache::has(ReceivableFigures::key($asOn) . ':busy'));
    }

    public function test_scheduled_every_fifteen_minutes()
    {
        $events = collect(app(\Illuminate\Console\Scheduling\Schedule::class)->events())->filter(function ($e) {
            return strpos($e->command, 'receivables:refresh-followup') !== false;
        });
        $this->assertCount(1, $events);
        $this->assertSame('*/15 * * * *', $events->first()->expression);
    }

    public function test_compute_asks_for_receipts_up_to_today()
    {
        $fake = new class extends \Modules\BackOffice\Services\RentDues {
            public $args;
            public function forDate($asOn, $receiptsUpTo = null, array $filters = [])
            {
                $this->args = [$asOn, $receiptsUpTo];
                return [];
            }
        };
        $this->app->instance(\Modules\BackOffice\Services\RentDues::class, $fake);
        Carbon::setTestNow('2026-10-02');
        try {
            (new ReceivableFigures)->compute('2026-07-31');
        } finally {
            Carbon::setTestNow();
        }
        $this->assertSame(['2026-07-31', '2026-10-02'], $fake->args);
    }
}
