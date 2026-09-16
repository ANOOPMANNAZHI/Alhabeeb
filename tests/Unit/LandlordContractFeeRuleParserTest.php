<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Modules\BackOffice\Services\LandlordContractFeeRuleParser;

class LandlordContractFeeRuleParserTest extends TestCase
{
    private function fee($phrase, $cleaning = '0')
    {
        return LandlordContractFeeRuleParser::parse($phrase, $cleaning);
    }

    public function test_percentage_per_month()
    {
        $r = $this->fee('5% P. M.');
        $this->assertSame(1, $r['management_method']);
        $this->assertSame(5.0, $r['landlord_contract_management_fee']);
        $this->assertNull($r['landlord_contract_facility_management_fee']);
        $this->assertNull($r['landlord_contract_renewal_fee']);
        $this->assertNull($r['landlord_contract_new_leasing_fee_type']);
        $this->assertNull($r['landlord_contract_new_leasing_fee']);

        $this->assertSame(3.5, $this->fee('3.5% P.M.')['landlord_contract_management_fee']);
        $this->assertSame(4.5, $this->fee('4.5% P. M.')['landlord_contract_management_fee']);
    }

    public function test_fixed_amount_per_month_in_all_spellings()
    {
        foreach (['400/- P. M.' => 400.0, '1,500/- P.M.' => 1500.0, '50 P.M.' => 50.0, '99/- P.M.' => 99.0] as $phrase => $expected) {
            $r = $this->fee($phrase);
            $this->assertSame(2, $r['management_method'], $phrase);
            $this->assertSame($expected, $r['landlord_contract_management_fee'], $phrase);
        }
    }

    public function test_fixed_amount_per_annum_is_divided_by_twelve()
    {
        $r = $this->fee('100/- P.A.');
        $this->assertSame(2, $r['management_method']);
        $this->assertSame(8.333, $r['landlord_contract_management_fee']);
    }

    public function test_renewal_fee_variants()
    {
        $this->assertSame(15.0, $this->fee('6% P. M. & RO 15/- for Renwal')['landlord_contract_renewal_fee']);
        $this->assertSame(25.0, $this->fee('5% P. M. + Each Renewal RO 25')['landlord_contract_renewal_fee']);
        $this->assertSame(25.0, $this->fee('99/- P.M. & RO 25 on Renewal & 4% on new Leasing')['landlord_contract_renewal_fee']);
    }

    public function test_new_leasing_fee_as_amount_or_percentage()
    {
        $r = $this->fee('125/- P. M. & RO 20/- for New Lease');
        $this->assertSame(2, $r['landlord_contract_new_leasing_fee_type']);
        $this->assertSame(20.0, $r['landlord_contract_new_leasing_fee']);

        $r = $this->fee('100/- P. M. & 4% on New Leasing');
        $this->assertSame(1, $r['landlord_contract_new_leasing_fee_type']);
        $this->assertSame(4.0, $r['landlord_contract_new_leasing_fee']);

        $r = $this->fee('3% P. M. & 3% on new Leasing');
        $this->assertSame(1, $r['management_method']);
        $this->assertSame(3.0, $r['landlord_contract_management_fee']);
        $this->assertSame(1, $r['landlord_contract_new_leasing_fee_type']);
        $this->assertSame(3.0, $r['landlord_contract_new_leasing_fee']);
    }

    public function test_facility_management_fee()
    {
        $r = $this->fee('5% P. M. + 65/- P.M. (Facility Management Fees)');
        $this->assertSame(1, $r['management_method']);
        $this->assertSame(5.0, $r['landlord_contract_management_fee']);
        $this->assertSame(65.0, $r['landlord_contract_facility_management_fee']);
    }

    public function test_three_components_together()
    {
        $r = $this->fee('300/- P. M. & 4% on New Leasing & RO 20/- for Renewal');
        $this->assertSame(2, $r['management_method']);
        $this->assertSame(300.0, $r['landlord_contract_management_fee']);
        $this->assertSame(1, $r['landlord_contract_new_leasing_fee_type']);
        $this->assertSame(4.0, $r['landlord_contract_new_leasing_fee']);
        $this->assertSame(20.0, $r['landlord_contract_renewal_fee']);
    }

    public function test_cleaning_charge_blank_means_unchanged()
    {
        $this->assertSame(25.0, $this->fee('5% P. M.', '25')['landlord_contract_cleaning_charge']);
        $this->assertSame(0.0, $this->fee('5% P. M.', '0')['landlord_contract_cleaning_charge']);
        $this->assertNull($this->fee('5% P. M.', '')['landlord_contract_cleaning_charge']);
    }

    public function test_unsupported_rule_throws()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->fee('30/- P. VILLA & 1,800/- P.A.');
    }

    public function test_missing_management_fee_throws()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->fee('RO 20/- for Renewal');
    }
}
