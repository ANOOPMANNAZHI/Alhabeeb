<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Modules\BackOffice\Services\UnpaidRentPeriod;

class UnpaidRentPeriodTest extends TestCase
{
    public function test_never_paid_runs_from_contract_start_to_termination()
    {
        $p = UnpaidRentPeriod::describe('2025-09-01', null, '2026-08-31');

        $this->assertSame('Sep 2025', $p['from']);
        $this->assertSame('Aug 2026', $p['to']);
        $this->assertSame(12, $p['months']);
        $this->assertSame('Unpaid: Sep 2025 - Aug 2026 (12 months) · no rent receipt on this contract', $p['label']);
    }

    public function test_paid_till_starts_the_period_the_following_day()
    {
        $p = UnpaidRentPeriod::describe('2025-09-01', '2026-05-31', '2026-08-31');

        $this->assertSame('Jun 2026', $p['from']);
        $this->assertSame('Aug 2026', $p['to']);
        $this->assertSame(3, $p['months']);
        $this->assertStringContainsString('paid till 31/05/2026', $p['label']);
    }

    public function test_part_month_counts_as_a_month()
    {
        // paid till 10 Jun -> unpaid 11 Jun .. 31 Aug = Jun, Jul, Aug
        $p = UnpaidRentPeriod::describe('2025-09-01', '2026-06-10', '2026-08-31');
        $this->assertSame('Jun 2026', $p['from']);
        $this->assertSame(3, $p['months']);
    }

    public function test_single_month_is_shown_once()
    {
        $p = UnpaidRentPeriod::describe('2025-09-01', '2026-07-31', '2026-08-31');
        $this->assertSame(1, $p['months']);
        $this->assertSame('Unpaid: Aug 2026 (1 month) · paid till 31/07/2026', $p['label']);
    }

    public function test_paid_up_to_or_beyond_termination_has_no_unpaid_period()
    {
        $p = UnpaidRentPeriod::describe('2025-09-01', '2026-08-31', '2026-08-31');
        $this->assertSame(0, $p['months']);
        $this->assertSame('Rent paid till 31/08/2026', $p['label']);

        $p = UnpaidRentPeriod::describe('2025-09-01', '2026-10-31', '2026-08-31');
        $this->assertSame(0, $p['months']);
    }

    public function test_missing_dates_give_empty_result()
    {
        $p = UnpaidRentPeriod::describe(null, null, '2026-08-31');
        $this->assertSame(0, $p['months']);
        $this->assertSame('', $p['label']);
    }
}
