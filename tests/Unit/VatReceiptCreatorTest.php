<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Modules\BackOffice\Services\VatReceiptCreator;

if (!defined('AX_DIVISION_HO')) {
    define('AX_DIVISION_HO', '01');
}
if (!defined('AX_DIVISION_PLMS')) {
    define('AX_DIVISION_PLMS', '02');
}

class VatReceiptCreatorTest extends TestCase
{
    public function test_remark_matches_the_teams_general_receipt_format()
    {
        $this->assertSame(
            'VAT for 16.08.26 to 15.09.26 UNIT NO. S 03 , Teeb Complex',
            VatReceiptCreator::remark('2026-08-16 00:00:00', '2026-09-15', 'S 03', 'Teeb Complex')
        );
    }

    public function test_narration_is_the_period_only()
    {
        $this->assertSame('VAT for 01.09.26 to 30.09.26', VatReceiptCreator::narration('2026-09-01', '2026-09-30'));
    }

    public function test_cheque_no_uses_pdc_bank_code_and_double_underscore()
    {
        $this->assertSame('BMI__81742436', VatReceiptCreator::chequeNo(' BMI ', '81742436'));
    }

    public function test_division_is_head_office_only_for_01()
    {
        $this->assertSame(AX_DIVISION_HO, VatReceiptCreator::division('01'));
        $this->assertSame(AX_DIVISION_PLMS, VatReceiptCreator::division('02'));
        $this->assertSame(AX_DIVISION_PLMS, VatReceiptCreator::division('2'));
        $this->assertSame(AX_DIVISION_PLMS, VatReceiptCreator::division(null));
    }
}
