<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Modules\BackOffice\Services\ReceiptNarration;

class ReceiptNarrationTest extends TestCase
{
    /** CAR2601616: cash that replaced cheque BMI 78624832. */
    private function cashAgainstCheque(array $o = [])
    {
        return array_merge([
            'remark'           => 'Received cash payment for cheque no 78624832',
            'description'      => null,
            'is_pdc_bounce_id' => 516672,
            'unit_no'          => '31',
            'building_name'    => 'Aashirwad Bldg',
            'rent'             => '150',
            'eff_from'         => '2026-09-01 00:00:00',
            'eff_to'           => '2026-09-30 00:00:00',
            'bank_code'        => 'BMI',
            'cheque_no'        => '78624832',
            'cheque_date'      => '2026-09-05',
        ], $o);
    }

    public function test_rent_being_matches_the_printed_receipt()
    {
        $this->assertSame(
            'RENT FOR UNIT NO. 31,Aashirwad Bldg FOR 01-09-2026 TO 30-09-2026@150/-PM',
            ReceiptNarration::rentBeing('31', 'Aashirwad Bldg', '150', '2026-09-01 00:00:00', '2026-09-30')
        );
        $this->assertSame('RENT FOR UNIT NO. , FOR  TO @/-PM', ReceiptNarration::rentBeing(null, null, null, null, null));
    }

    public function test_cash_against_cheque_comment_matches_the_printed_receipt()
    {
        $this->assertSame(' Cash/B.T against BMI78624832 dated 2026-09-05',
            ReceiptNarration::cashAgainstChequeComment('BMI', '78624832', '2026-09-05 00:00:00'));
    }

    public function test_cash_against_cheque_posts_rent_text_and_cheque_note()
    {
        $ax = ReceiptNarration::forAxPosting($this->cashAgainstCheque());
        $this->assertSame('RENT FOR UNIT NO. 31,Aashirwad Bldg FOR 01-09-2026 TO 30-09-2026@150/-PM', $ax['description']);
        $this->assertSame('Cash/B.T against BMI78624832 dated 2026-09-05', $ax['remarks']);
        $this->assertSame('Cash/B.T against BMI78624832 dated 2026-09-05', $ax['remarks_alt']);
    }

    public function test_normal_receipt_posts_exactly_what_it_posts_today()
    {
        $ax = ReceiptNarration::forAxPosting($this->cashAgainstCheque([
            'remark'           => 'RENT FOR UNIT NO. 4,Teeb FOR 01-09-2026 TO 30-09-2026@200/-PM',
            'description'      => 'some description',
            'is_pdc_bounce_id' => 0,
        ]));
        $this->assertSame('RENT FOR UNIT NO. 4,Teeb FOR 01-09-2026 TO 30-09-2026@200/-PM', $ax['description']);
        $this->assertSame('some description', $ax['remarks']);
        $this->assertSame('RENT FOR UNIT NO. 4,Teeb FOR 01-09-2026 TO 30-09-2026@200/-PM', $ax['remarks_alt']);

        $this->assertSame($ax, ReceiptNarration::forAxPosting($this->cashAgainstCheque([
            'remark' => 'RENT FOR UNIT NO. 4,Teeb FOR 01-09-2026 TO 30-09-2026@200/-PM',
            'description' => 'some description', 'is_pdc_bounce_id' => null])));
    }

    public function test_linked_cheque_missing_still_posts_rent_text_and_keeps_stored_remark_as_note()
    {
        $ax = ReceiptNarration::forAxPosting($this->cashAgainstCheque(['cheque_no' => null, 'bank_code' => null, 'cheque_date' => null]));
        $this->assertSame('RENT FOR UNIT NO. 31,Aashirwad Bldg FOR 01-09-2026 TO 30-09-2026@150/-PM', $ax['description']);
        $this->assertSame('Received cash payment for cheque no 78624832', $ax['remarks']);
    }
}
