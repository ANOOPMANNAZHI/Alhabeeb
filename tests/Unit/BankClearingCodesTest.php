<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Modules\BackOffice\Services\BankClearingCodes;
use Modules\BackOffice\Services\BankSheetPdcMatcher;

class BankClearingCodesTest extends TestCase
{
    public function test_add_keeps_the_codes_already_saved()
    {
        $this->assertSame('45', BankClearingCodes::add(null, '45'));
        $this->assertSame('18,45', BankClearingCodes::add('18', '45'));
        $this->assertSame('27,34,41,45', BankClearingCodes::add('27, 34,41', '45'));
        $this->assertSame('27,34', BankClearingCodes::add('27,34', '34'));   // already there: unchanged
        $this->assertSame(['27', '34'], BankClearingCodes::split(' 27 ,,34,27'));
    }

    public function test_sheet_name_and_valid_code()
    {
        $this->assertSame('Commercial Bank of Oman', BankClearingCodes::sheetName('45 - Commercial Bank of Oman'));
        $this->assertTrue(BankClearingCodes::valid('45'));
        $this->assertFalse(BankClearingCodes::valid('Some Bank'));
        $this->assertFalse(BankClearingCodes::valid(''));
    }

    private function row($code, $amount, $status = BankSheetPdcMatcher::UNKNOWN_BANK, array $related = [])
    {
        $r = ['status' => $status, 'row' => ['bank_code' => $code, 'bank_name' => $code . ' - Commercial Bank', 'amount' => $amount]];
        if ($related) {
            $r['related'] = $related;
            $r['related_kind'] = BankSheetPdcMatcher::RELATED_OTHER_BANK;
        }
        return $r;
    }

    private function pdc($bankId, $amount)
    {
        return (object) ['bank_id' => $bankId, 'pdc_amt' => $amount];
    }

    public function test_unknown_codes_are_counted_and_the_bank_suggested_from_matching_cheques()
    {
        $out = BankClearingCodes::unknown([
            $this->row('45', 200, BankSheetPdcMatcher::UNKNOWN_BANK, [$this->pdc(16, 200)]),
            $this->row('45', 300, BankSheetPdcMatcher::UNKNOWN_BANK, [$this->pdc(16, 300), $this->pdc(3, 120)]),   // only bank 16 has the amount
            $this->row('45', 400),                                                                               // not in PLMS: no vote
            $this->row('50', 100, BankSheetPdcMatcher::UNKNOWN_BANK, [$this->pdc(6, 100), $this->pdc(7, 100)]),     // two banks: no vote
            $this->row('27', 100, BankSheetPdcMatcher::MATCHED),                                                 // known code: ignored
            $this->row('Some Bank', 100),                                                                        // no number: nothing to save
        ]);
        $this->assertSame(['45', '50'], array_map('strval', array_keys($out)));
        $this->assertSame(3, $out['45']['rows']);
        $this->assertSame('Commercial Bank', $out['45']['sheet_name']);
        $this->assertSame(16, $out['45']['suggested_bank_id']);
        $this->assertNull($out['50']['suggested_bank_id']);
    }

    public function test_split_cheque_votes_and_ties_suggest_nothing()
    {
        $out = BankClearingCodes::unknown([
            $this->row('45', 300, BankSheetPdcMatcher::UNKNOWN_BANK, [$this->pdc(16, 100), $this->pdc(16, 200)]),   // 100 + 200 on bank 16
        ]);
        $this->assertSame(16, $out['45']['suggested_bank_id']);

        $tie = BankClearingCodes::unknown([
            $this->row('45', 100, BankSheetPdcMatcher::UNKNOWN_BANK, [$this->pdc(16, 100)]),
            $this->row('45', 200, BankSheetPdcMatcher::UNKNOWN_BANK, [$this->pdc(3, 200)]),
        ]);
        $this->assertNull($tie['45']['suggested_bank_id']);
    }
}
