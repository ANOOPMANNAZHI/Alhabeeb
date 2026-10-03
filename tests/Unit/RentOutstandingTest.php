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
    public function test_short_contract_is_capped_at_contract_value()
    {
        // TAG2601977: 15/09/2026 -> 30/09/2026 (16 days), rent 250, value 133.333.
        // One started month would be 250; the tenant can never owe more than the value.
        $r = RentOutstanding::compute('2026-09-15', '2026-09-30', 250, 0, '133.333', '2026-09-30');
        $this->assertSame(133.333, $r['rent_due']);
        $this->assertSame(133.333, $r['outstanding']);

        $paid = RentOutstanding::compute('2026-09-15', '2026-09-30', 250, 133.333, '133.333', '2026-09-30');
        $this->assertSame(0.0, $paid['outstanding']);
        $this->assertSame(0.0, $paid['overpaid']);
    }

    public function test_contract_ending_part_way_through_a_month_is_trimmed()
    {
        // 12 started months = 1350 at 112.5; value 1260 (the last month is partial)
        $this->assertSame(1260.0, RentOutstanding::contractValueCap('2021-01-10', '2021-12-31', 112.5, '1260'));
        $this->assertSame(1260.0, RentOutstanding::compute('2021-01-10', '2021-12-31', 112.5, 0, '1260', '2021-12-31')['rent_due']);
        // part way through the contract: months x rent is already below the value -> unchanged
        $this->assertSame(225.0, RentOutstanding::compute('2021-01-10', '2021-02-15', 112.5, 0, '1260', '2021-12-31')['rent_due']);
    }

    public function test_value_that_is_not_the_contract_total_is_ignored()
    {
        // TAG1901408: 12 x 1400 = 16800 but value holds one month (1400) -> not a trim, ignore it
        $this->assertNull(RentOutstanding::contractValueCap('2019-01-01', '2019-12-31', 1400, '1400'));
        $this->assertSame(16800.0, RentOutstanding::compute('2019-01-01', '2019-12-31', 1400, 0, '1400', '2019-12-31')['rent_due']);
    }

    public function test_no_cap_without_value_or_contract_end()
    {
        $this->assertNull(RentOutstanding::contractValueCap('2026-09-15', '2026-09-30', 250, null));
        $this->assertNull(RentOutstanding::contractValueCap('2026-09-15', '2026-09-30', 250, '0'));
        $this->assertNull(RentOutstanding::contractValueCap('2026-09-15', null, 250, '133.333'));
        $this->assertSame(250.0, RentOutstanding::compute('2026-09-15', '2026-09-30', 250, 0)['rent_due']);
        // value equal to the full term (normal contract) -> cap equals the full rent, nothing changes
        $this->assertSame(2280.0, RentOutstanding::compute('2025-09-01', '2026-08-31', 190, 0, '2,280.000', '2026-08-31')['rent_due']);
    }

    public function test_cap_reduction_at_termination_date()
    {
        // TAG2601977: 250 by started months, 133.333 by value -> the rule removes 116.667
        $this->assertSame(116.667, RentOutstanding::capReduction('2026-09-15', '2026-09-30', 250, '133.333', '2026-09-30'));
        // normal contract / untrusted value -> nothing to correct
        $this->assertSame(0.0, RentOutstanding::capReduction('2025-09-01', '2026-08-31', 190, '2280', '2026-08-31'));
        $this->assertSame(0.0, RentOutstanding::capReduction('2019-01-01', '2019-12-31', 1400, '1400', '2019-12-31'));
    }

    public function test_corrected_stored_amount_is_idempotent()
    {
        // stored under the old rule (250, nothing paid) -> 133.333
        $this->assertSame(133.333, RentOutstanding::correctedStoredAmount(250, 0, 133.333, 116.667));
        // already correct (stored after the fix, or a second run) -> unchanged
        $this->assertSame(133.333, RentOutstanding::correctedStoredAmount(133.333, 0, 133.333, 116.667));
        // stored 250 before a 133.333 receipt was approved: still only the rule's reduction
        $this->assertSame(133.333, RentOutstanding::correctedStoredAmount(250, 133.333, 133.333, 116.667));
        // never below zero
        $this->assertSame(0.0, RentOutstanding::correctedStoredAmount(100, 0, 0, 150));
        // null stays null (nothing stored)
        $this->assertNull(RentOutstanding::correctedStoredAmount(null, 0, 133.333, 116.667));
    }

    public function test_stored_os_may_stay_negative_when_not_floored()
    {
        // tenant_contract_os is saved as due - receipts without a floor: -20 means overpaid by 20.
        // TAG2600006: old due 133.333+20 paid... the 13.333 reduction makes the overpayment 33.333.
        $this->assertSame(-33.333, RentOutstanding::correctedStoredAmount(-20, 140, 106.667, 13.333, false));
        $this->assertSame(-6.667, RentOutstanding::correctedStoredAmount(93.333, 306.667, 300, 100, false));
        // already correct negative -> unchanged
        $this->assertSame(-33.333, RentOutstanding::correctedStoredAmount(-33.333, 140, 106.667, 13.333, false));
    }

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

    /** Option A (03/10/2026): early terminations from 01/08/2026 use the reports' 30-day rule. */
    public function test_early_termination_from_the_cut_over_is_charged_by_the_day()
    {
        // TAG2602074: 01/09 -> 15/09, rent 240: 15 days = 120, not a full month
        $r = RentOutstanding::compute('2026-09-01', '2026-09-15', 240, 0, '2880', '2027-08-31', true);
        $this->assertSame(120.0, $r['rent_due']);
        $this->assertSame(120.0, $r['outstanding']);
        // a full calendar month is still one month's rent
        $this->assertSame(650.0, RentOutstanding::compute('2026-08-01', '2026-08-31', 650, 0, '6500', '2027-05-31', true)['rent_due']);
        // TAG2601302 16/06 -> 31/08: 2 months + 16 days at 725 = 1,837, more than the whole
        // contract (16/06 -> 15/09, value 1,812.5): never more than the contract value
        $this->assertSame(1812.5, RentOutstanding::compute('2026-06-16', '2026-08-31', 725, 0, '1812.5', '2026-09-15', true)['rent_due']);
    }

    public function test_termination_on_the_start_date_owes_nothing_and_payments_show_as_overpaid()
    {
        // TAG2601464: terminated on its start date 01/10/2026, 605.714 paid
        $r = RentOutstanding::compute('2026-10-01', '2026-10-01', 650, 605.714, '7800', '2027-09-30', true);
        $this->assertSame(0.0, $r['rent_due']);
        $this->assertSame(0.0, $r['outstanding']);
        $this->assertSame(605.714, $r['overpaid']);
    }

    public function test_unchanged_before_the_cut_over_at_the_contract_end_and_when_not_terminated()
    {
        // terminated before 01/08/2026: a started month still counts as a whole month
        $this->assertSame(300.0, RentOutstanding::compute('2026-07-01', '2026-07-15', 300, 0, '3600', '2027-06-30', true)['rent_due']);
        // terminated on the contract end date: unchanged
        $this->assertSame(650.0, RentOutstanding::compute('2026-08-01', '2026-08-31', 650, 0, '650', '2026-08-31', true)['rent_due']);
        // not a termination (as of today): unchanged
        $this->assertSame(240.0, RentOutstanding::compute('2026-09-01', '2026-09-15', 240, 0, '2880', '2027-08-31')['rent_due']);
    }
}
