<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Modules\BackOffice\Services\BankSheetPostedCheques as P;

class BankSheetPostedChequesTest extends TestCase
{
    private $bankMap = ['27' => [3, 9], '18' => 5];

    private function row(array $o = [])
    {
        return array_merge(['row_no' => 4, 'cheque_no' => '00205886', 'bank_code' => '27', 'bank_name' => '27 - Bank Of Muscat',
                            'amount' => 500.0, 'cheque_date' => '2026-10-01'], $o);
    }

    private function pdc(array $o = [])
    {
        static $id = 900;
        $id++;
        return (object) array_merge(['id' => $id, 'pdc_check_no' => '205886', 'bank_id' => 3, 'pdc_amt' => 500.0,
            'pdc_check_date' => '2026-10-01'], $o);
    }

    public function test_posted_cheque_same_month_is_found()
    {
        $out = P::find([$this->row()], [$this->pdc()], $this->bankMap);
        $this->assertArrayHasKey(4, $out);
        $this->assertTrue($out[4]['same_amount']);
        $this->assertSame(500.0, $out[4]['amount']);
    }

    public function test_split_rent_and_vat_rows_add_up()
    {
        $out = P::find([$this->row(['amount' => 525.0])],
            [$this->pdc(), $this->pdc(['pdc_amt' => 25.0, 'bank_id' => 9])], $this->bankMap);
        $this->assertCount(2, $out[4]['pdcs']);
        $this->assertTrue($out[4]['same_amount']);
    }

    public function test_other_bank_or_unmapped_bank_is_not_posted()
    {
        $this->assertSame([], P::find([$this->row()], [$this->pdc(['bank_id' => 5])], $this->bankMap));
        $this->assertSame([], P::find([$this->row(['bank_code' => '99'])], [$this->pdc()], $this->bankMap));
    }

    public function test_earlier_months_within_window_nearest_first()
    {
        $out = P::find([$this->row()], [
            $this->pdc(['pdc_check_date' => '2026-07-01', 'pdc_amt' => 100.0]),
            $this->pdc(['pdc_check_date' => '2026-09-01']),
        ], $this->bankMap);
        $this->assertCount(1, $out[4]['pdcs']);
        $this->assertSame('2026-09-01', $out[4]['pdcs'][0]->pdc_check_date);
    }

    public function test_out_of_window_is_not_posted()
    {
        // last year's cheque with the same number, or a later one
        $this->assertSame([], P::find([$this->row()], [
            $this->pdc(['pdc_check_date' => '2025-10-01']),
            $this->pdc(['pdc_check_date' => '2026-06-30']),
            $this->pdc(['pdc_check_date' => '2026-11-01']),
        ], $this->bankMap));
    }

    public function test_amount_difference_is_flagged()
    {
        $out = P::find([$this->row()], [$this->pdc(['pdc_amt' => 450.0])], $this->bankMap);
        $this->assertFalse($out[4]['same_amount']);
        $this->assertSame(450.0, $out[4]['amount']);
    }
}
