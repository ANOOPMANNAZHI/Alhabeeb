<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Modules\BackOffice\Services\VatPdcClassifier as C;

class VatPdcClassifierTest extends TestCase
{
    private function pdc(array $o = [])
    {
        static $id = 3000;
        $id++;
        return (object) array_merge(['id' => $id, 'tenant_contract_id' => 7, 'pdc_check_no' => '100', 'bank_id' => 3,
            'pdc_amt' => 1000.0, 'pdc_check_date' => '2026-10-01'], $o);
    }

    public function test_others_on_rent_cheque_near_five_percent_is_vat()
    {
        $rent = $this->pdc();
        $vat  = $this->pdc(['pdc_check_no' => '00100', 'pdc_amt' => 50.0]);
        $out = C::classify([$vat], [$rent]);
        $this->assertSame(C::SAME_CHEQUE, $out[$vat->id]['kind']);
        $this->assertSame(50.0, $out[$vat->id]['expected']);
    }

    public function test_charge_on_rent_cheque_stays_others()
    {
        $out = C::classify([$this->pdc(['pdc_amt' => 135.0])], [$this->pdc()]);
        $this->assertSame([], $out);
    }

    public function test_vat_and_charge_on_one_rent_cheque_picks_only_the_vat()
    {
        $vat = $this->pdc(['pdc_amt' => 50.0]);
        $fee = $this->pdc(['pdc_amt' => 26.25]);
        $out = C::classify([$vat, $fee], [$this->pdc()]);
        $this->assertSame([$vat->id], array_keys($out));
    }

    public function test_own_cheque_near_five_percent_of_same_month_rent_is_vat()
    {
        $vat = $this->pdc(['pdc_check_no' => '900', 'pdc_amt' => 50.5, 'pdc_check_date' => '2026-10-05']);
        $out = C::classify([$vat], [$this->pdc(), $this->pdc(['pdc_amt' => 2000.0, 'pdc_check_date' => '2026-11-01'])]);
        $this->assertSame(C::OWN_CHEQUE, $out[$vat->id]['kind']);
        $this->assertSame(1000.0, $out[$vat->id]['rent']);
    }

    public function test_own_cheque_uses_nearest_rent_when_month_has_none()
    {
        $vat = $this->pdc(['pdc_check_no' => '900', 'pdc_amt' => 100.0, 'pdc_check_date' => '2026-12-20']);
        $out = C::classify([$vat], [$this->pdc(), $this->pdc(['pdc_amt' => 2000.0, 'pdc_check_date' => '2027-01-01'])]);
        $this->assertSame(2000.0, $out[$vat->id]['rent']);
    }

    public function test_own_cheque_charge_or_other_agreement_is_not_vat()
    {
        $this->assertSame([], C::classify([$this->pdc(['pdc_check_no' => '900', 'pdc_amt' => 300.0])], [$this->pdc()]));
        $this->assertSame([], C::classify([$this->pdc(['pdc_check_no' => '900', 'pdc_amt' => 50.0, 'tenant_contract_id' => 8])], [$this->pdc()]));
    }

    public function test_band_is_one_rial_or_half_percent_of_rent()
    {
        $this->assertTrue(C::nearVat(15.3, 280.0));    // 14 ± 1.4 (0.5% of 280 beats 1.000)
        $this->assertFalse(C::nearVat(15.5, 280.0));
        $this->assertFalse(C::nearVat(6.1, 100.0));    // 5 ± 1.000
        $this->assertTrue(C::nearVat(510.0, 10000.0)); // 500 ± 50
        $this->assertFalse(C::nearVat(551.0, 10000.0));
    }
}
