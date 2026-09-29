<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Modules\Masters\Entities\PdcBounceReason as B;

/** Bank-sheet rejection text -> bounce reason, driven by the master's "bank sheet words". */
class PdcBounceReasonMatchTest extends TestCase
{
    /** The starting data as matchers (what the migration seeds). */
    private function matchers()
    {
        $out = [];
        foreach (B::DEFAULTS as $d) {
            if ($d['is_active']) {
                $out[] = ['id' => $d['id'], 'keywords' => B::splitKeywords($d['bank_keywords']), 'fallback' => $d['is_fallback'], 'sort_order' => $d['sort_order']];
            }
        }
        return $out;
    }

    public function test_starting_data_keeps_the_nine_reasons_and_adds_account_closed()
    {
        $byId = [];
        foreach (B::DEFAULTS as $d) {
            $byId[$d['id']] = $d;
        }
        $this->assertSame('Insufficient Funds', $byId[1]['name']);
        $this->assertSame('Stale Cheque (Beyond six months)', $byId[8]['name']);
        $this->assertSame('Misc', $byId[9]['name']);
        $this->assertFalse($byId[9]['is_active']);
        $this->assertSame('Account Closed / Frozen / Dormant', $byId[10]['name']);
        $this->assertTrue($byId[10]['is_active']);
    }

    public function test_bank_texts_from_real_sheets()
    {
        $m = $this->matchers();
        $this->assertSame(1, B::match('1 - 001-Insufficient Fund', $m));
        $this->assertSame(1, B::match('22 - 022-NSF/ Eligible for PPS', $m));
        $this->assertSame(1, B::match('FUNDS NOT SUFFICIENT', $m));
        $this->assertSame(3, B::match('11 - 011- Signature Required / Unmatched / Additional Signature(s) Required', $m));
        $this->assertSame(10, B::match('12 - 012- Account Closed / Frozen / Dormant', $m));
    }

    public function test_previous_built_in_rules_still_hold()
    {
        $m = $this->matchers();
        $this->assertSame(2, B::match('Signature missing', $m));
        $this->assertSame(3, B::match('Signature differs', $m));
        $this->assertSame(4, B::match('Amount in words and figures differ', $m));
        $this->assertSame(5, B::match('Payment stopped by drawer', $m));
        $this->assertSame(6, B::match('Refer to drawer', $m));
        $this->assertSame(7, B::match('Alteration on cheque', $m));
        $this->assertSame(8, B::match('Stale cheque', $m));
    }

    public function test_no_match_gives_no_reason_now_that_misc_is_off()
    {
        $m = $this->matchers();
        $this->assertNull(B::match('Some unknown bank reason', $m));
        $this->assertNull(B::match('No clearing reason on sheet', $m));
        $this->assertNull(B::match('', $m));
    }

    public function test_fallback_reason_is_used_when_one_is_set()
    {
        $m = [['id' => 1, 'keywords' => ['nsf'], 'fallback' => false, 'sort_order' => 1],
              ['id' => 9, 'keywords' => [], 'fallback' => true, 'sort_order' => 9]];
        $this->assertSame(9, B::match('Something else', $m));
        $this->assertSame(1, B::match('NSF', $m));
    }

    public function test_longest_matching_word_wins()
    {
        $m = [['id' => 3, 'keywords' => ['signature'], 'fallback' => false, 'sort_order' => 3],
              ['id' => 2, 'keywords' => ['signature missing'], 'fallback' => false, 'sort_order' => 2]];
        $this->assertSame(2, B::match('SIGNATURE MISSING', $m));
    }

    public function test_split_keywords()
    {
        $this->assertSame(['account closed', 'frozen', '012'], B::splitKeywords(" Account Closed, frozen ,,012 "));
        $this->assertSame([], B::splitKeywords(null));
    }
}
