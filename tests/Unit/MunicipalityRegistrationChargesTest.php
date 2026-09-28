<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Modules\BackOffice\Services\MunicipalityRegistrationCharges as C;

class MunicipalityRegistrationChargesTest extends TestCase
{
    public function test_money_parses_varchar_values()
    {
        $this->assertSame(3600.5, C::money('3,600.500'));
        $this->assertSame(1920.0, C::money('1920'));
        $this->assertSame(0.0, C::money(''));
        $this->assertSame(0.0, C::money(null));
        $this->assertSame(0.0, C::money('abc'));
        $this->assertSame(1.235, C::money('1.2345'));
    }

    public function test_default_tax_is_value_times_rate()
    {
        $this->assertSame(57.6, C::defaultTax('1920', 3));
        $this->assertSame(108.0, C::defaultTax('3,600.000', '3'));
        $this->assertSame(0.0, C::defaultTax('', 3));
        $this->assertSame(0.0, C::defaultTax('0', 3));
        $this->assertSame(10.001, C::defaultTax('333.367', 3));
    }

    public function test_same_amount_ignores_formatting()
    {
        $this->assertTrue(C::sameAmount('108', '108.000'));
        $this->assertTrue(C::sameAmount(108.0, '108.0004'));
        $this->assertFalse(C::sameAmount('108', '108.01'));
    }

    public function test_description_drops_blanks()
    {
        $this->assertSame('M.TAX TAG2301963 Teeb Complex S 03', C::description('M.TAX', 'TAG2301963', 'Teeb Complex', 'S 03'));
        $this->assertSame('M.TAX PENALTY TAG1', C::description('M.TAX PENALTY', 'TAG1', '', null));
    }

    public function test_voucher_lines_balance_and_carry_dims()
    {
        $lines = C::voucherLines(58.6, 747, 1, 250, 5300);
        $this->assertCount(2, $lines);

        $this->assertSame(747, $lines[0]['account_id']);
        $this->assertSame(58.6, $lines[0]['debit_amt']);
        $this->assertSame(0.0, $lines[0]['credit_amt']);

        $this->assertSame(1, $lines[1]['account_id']);
        $this->assertSame(0.0, $lines[1]['debit_amt']);
        $this->assertSame(58.6, $lines[1]['credit_amt']);

        foreach ($lines as $l) {
            $this->assertSame(250, $l['building_id']);
            $this->assertSame(5300, $l['unit_id']);
            $this->assertSame(C::DIVISION_DIM_ID, $l['dim1able_id']);
            $this->assertSame('Modules\BackOffice\Entities\DimDetail', $l['dim1able_type']);
            $this->assertSame('Modules\Masters\Entities\Building', $l['dim2able_type']);
            $this->assertSame(250, $l['dim2able_id']);
            $this->assertSame(0, $l['recovery']);
            $this->assertNull($l['jv_desc']);
        }
    }
}
