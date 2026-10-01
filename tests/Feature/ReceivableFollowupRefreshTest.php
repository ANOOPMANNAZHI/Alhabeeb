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
    protected function setUp()
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
        $this->assertSame([$this->lastMonthAsOn()], FakeFigures::$computed);
        $this->assertSame(10.0, Cache::get(ReceivableFigures::key($this->lastMonthAsOn()))['rows'][5]['balance']);
    }

    public function test_also_refreshes_today_when_opened_today()
    {
        $today = Carbon::today()->format('Y-m-d');
        Cache::put(ReceivableFigures::key($today), ['computed_at' => 'x', 'rows' => []], 10);
        $this->artisan('receivables:refresh-followup')->assertExitCode(0);
        $this->assertContains($today, FakeFigures::$computed);
    }

    public function test_failure_keeps_previous_figures()
    {
        $key = ReceivableFigures::key($this->lastMonthAsOn());
        Cache::put($key, ['computed_at' => 'old', 'rows' => []], 10);
        FakeFigures::$fail = true;
        $this->artisan('receivables:refresh-followup');
        $this->assertSame('old', Cache::get($key)['computed_at']);
    }

    public function test_scheduled_every_fifteen_minutes()
    {
        $events = collect(app(\Illuminate\Console\Scheduling\Schedule::class)->events())->filter(function ($e) {
            return strpos($e->command, 'receivables:refresh-followup') !== false;
        });
        $this->assertCount(1, $events);
        $this->assertSame('*/15 * * * *', $events->first()->expression);
    }
}
