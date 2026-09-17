<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Modules\BackOffice\Services\MunicipalTaxCalculator;

class MunicipalTaxCalculatorTest extends TestCase
{
    private function contract(array $o = [])
    {
        return (object) array_merge([
            'tenant_contract_value'         => 3600,
            'tenant_contract_rent'          => 300,
            'tenant_contract_valid_to_date' => '2026-12-31',
        ], $o);
    }

    private function termination(array $o = [])
    {
        return (object) array_merge([
            'termination_type_status' => 0,
            'termination_date'        => '2026-09-10',
        ], $o);
    }

    public function test_normal_termination_is_rate_on_total_contract_value()
    {
        $r = MunicipalTaxCalculator::forTermination($this->contract(), $this->termination(), 3);

        $this->assertSame(108.0, $r['amount']);
        $this->assertFalse($r['early']);
        $this->assertSame(0, $r['months']);
        $this->assertStringContainsString('3,600.000', $r['note']);
        $this->assertStringContainsString('3%', $r['note']);
    }

    public function test_approved_early_termination_counts_balance_months_rounded_up()
    {
        // 10 Sep -> 31 Dec = 3 months 21 days -> 4 months
        $r = MunicipalTaxCalculator::forTermination(
            $this->contract(), $this->termination(['termination_type_status' => 3]), 3
        );

        $this->assertTrue($r['early']);
        $this->assertSame(4, $r['months']);
        $this->assertSame(36.0, $r['amount']); // 4 x 300 x 3%
        $this->assertStringContainsString('4 month', $r['note']);
    }

    public function test_exact_whole_months_are_not_rounded_up()
    {
        // 30 Sep -> 31 Dec = exactly 3 months
        $r = MunicipalTaxCalculator::forTermination(
            $this->contract(), $this->termination(['termination_type_status' => 3, 'termination_date' => '2026-09-30']), 3
        );

        $this->assertSame(3, $r['months']);
        $this->assertSame(27.0, $r['amount']);
    }

    public function test_requested_and_under_approval_early_terminations_use_early_rule()
    {
        foreach ([1, 2] as $status) {
            $r = MunicipalTaxCalculator::forTermination(
                $this->contract(), $this->termination(['termination_type_status' => $status]), 3
            );
            $this->assertTrue($r['early'], "status $status");
        }
    }

    public function test_rejected_early_request_is_treated_as_normal()
    {
        $r = MunicipalTaxCalculator::forTermination(
            $this->contract(), $this->termination(['termination_type_status' => 4]), 3
        );

        $this->assertFalse($r['early']);
        $this->assertSame(108.0, $r['amount']);
    }

    public function test_early_termination_on_or_after_contract_end_has_no_balance()
    {
        $r = MunicipalTaxCalculator::forTermination(
            $this->contract(), $this->termination(['termination_type_status' => 3, 'termination_date' => '2027-01-15']), 3
        );

        $this->assertSame(0, $r['months']);
        $this->assertSame(0.0, $r['amount']);
    }

    public function test_zero_or_missing_rate_gives_zero()
    {
        $this->assertSame(0.0, MunicipalTaxCalculator::forTermination($this->contract(), $this->termination(), 0)['amount']);
        $this->assertSame(0.0, MunicipalTaxCalculator::forTermination($this->contract(), $this->termination(), null)['amount']);
    }

    public function test_amount_is_rounded_to_three_decimals()
    {
        $r = MunicipalTaxCalculator::forTermination($this->contract(['tenant_contract_value' => 1234.567]), $this->termination(), 3);
        $this->assertSame(37.037, $r['amount']);
    }
}
