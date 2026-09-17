<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Modules\BackOffice\Services\RentOutstanding;

/**
 * Rent due from the contract's effective date up to an "as of" date
 * (termination date), whole months, and the outstanding after payments.
 * Pure: no models, no database.
 */
class RentOutstandingTest extends TestCase
{
    public function test_full_year_contract_is_twelve_months()
    {
        // TAG2502151: 01/09/2025 -> 31/08/2026, rent 190. Legacy %m-only maths gave 11m 30d.
        $this->assertSame(12, RentOutstanding::monthsBetween('2025-09-01', '2026-08-31'));
        $this->assertSame(2280.0, RentOutstanding::rentDue('2025-09-01', '2026-08-31', 190));
    }

    public function test_whole_years_are_counted()
    {
        // 14 months: legacy code returned 2 (%m only)
        $this->assertSame(14, RentOutstanding::monthsBetween('2025-01-01', '2026-02-28'));
    }

    public function test_a_started_month_counts_as_a_whole_month()
    {
        $this->assertSame(1, RentOutstanding::monthsBetween('2025-09-01', '2025-09-01'));
        $this->assertSame(1, RentOutstanding::monthsBetween('2025-09-01', '2025-09-30'));
        $this->assertSame(2, RentOutstanding::monthsBetween('2025-09-01', '2025-10-01'));
        $this->assertSame(2, RentOutstanding::monthsBetween('2025-09-01', '2025-10-15'));
    }

    public function test_mid_month_start_dates()
    {
        // 15 Jan -> 14 Feb is exactly one month; 15 Jan -> 15 Feb starts a second
        $this->assertSame(1, RentOutstanding::monthsBetween('2026-01-15', '2026-02-14'));
        $this->assertSame(2, RentOutstanding::monthsBetween('2026-01-15', '2026-02-15'));
    }

    public function test_termination_before_effective_date_is_zero()
    {
        $this->assertSame(0, RentOutstanding::monthsBetween('2026-01-01', '2025-12-31'));
        $this->assertSame(0.0, RentOutstanding::rentDue('2026-01-01', '2025-12-31', 190));
    }

    public function test_missing_dates_or_rent_give_zero()
    {
        $this->assertSame(0, RentOutstanding::monthsBetween(null, '2026-01-01'));
        $this->assertSame(0, RentOutstanding::monthsBetween('2026-01-01', null));
        $this->assertSame(0.0, RentOutstanding::rentDue('2025-09-01', '2026-08-31', null));
    }

    public function test_compute_nets_payments_and_never_goes_negative()
    {
        $r = RentOutstanding::compute('2025-09-01', '2026-08-31', 190, 2280.0);
        $this->assertSame(12, $r['months']);
        $this->assertSame(2280.0, $r['rent_due']);
        $this->assertSame(2280.0, $r['paid']);
        $this->assertSame(0.0, $r['outstanding']);

        $r = RentOutstanding::compute('2025-09-01', '2026-08-31', 190, 2470.0);
        $this->assertSame(0.0, $r['outstanding']);
        $this->assertSame(190.0, $r['overpaid']);

        $r = RentOutstanding::compute('2025-09-01', '2026-08-31', 190, '1,900.000');
        $this->assertSame(380.0, $r['outstanding']);
        $this->assertSame(0.0, $r['overpaid']);
    }

    public function test_accepts_datetime_strings_and_rounds_to_three_decimals()
    {
        $this->assertSame(12, RentOutstanding::monthsBetween('2025-09-01 00:00:00', '2026-08-31 00:00:00'));
        // rent is a 3-dp money value: 111.3889 -> 111.389 before multiplying
        $this->assertSame(1336.668, RentOutstanding::rentDue('2025-09-01', '2026-08-31', 111.3889));
    }
}
