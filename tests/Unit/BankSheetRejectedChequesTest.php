<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Modules\BackOffice\Services\BankSheetRejectedCheques as R;

class BankSheetRejectedChequesTest extends TestCase
{
    private function row(array $o = [])
    {
        return array_merge(['row_no' => 7, 'cheque_no' => '00281048', 'bank_code' => '27', 'bank_name' => '27 - Bank Of Muscat',
                            'amount' => 250.0, 'cheque_date' => '2026-09-15', 'accepted' => false, 'reject_reason' => 'Insufficient Funds'], $o);
    }

    private function pdc(array $o = [])
    {
        static $id = 500;
        $id++;
        return (object) array_merge(['id' => $id, 'pdc_check_no' => '281048', 'bank_id' => 3, 'pdc_amt' => 250.0,
            'pdc_check_date' => '2026-09-15', 'tenant_contract_id' => 11, 'pdc_type' => 1, 'pdc_cancel_reason' => 0], $o);
    }

    public function test_bounce_reason_from_bank_text()
    {
        // the master's starting data (no database in unit tests)
        $m = [];
        foreach (\Modules\Masters\Entities\PdcBounceReason::DEFAULTS as $d) {
            if ($d['is_active']) {
                $m[] = ['id' => $d['id'], 'keywords' => \Modules\Masters\Entities\PdcBounceReason::splitKeywords($d['bank_keywords']),
                        'fallback' => $d['is_fallback'], 'sort_order' => $d['sort_order']];
            }
        }
        $this->assertSame(1, R::bounceReasonFor('Insufficient Funds', $m));
        $this->assertSame(1, R::bounceReasonFor('FUNDS NOT SUFFICIENT', $m));
        $this->assertSame(2, R::bounceReasonFor('Signature missing', $m));
        $this->assertSame(3, R::bounceReasonFor('Signature differs', $m));
        $this->assertSame(4, R::bounceReasonFor('Amount in words and figures differ', $m));
        $this->assertSame(5, R::bounceReasonFor('Payment stopped by drawer', $m));
        $this->assertSame(6, R::bounceReasonFor('Refer to drawer', $m));
        $this->assertSame(8, R::bounceReasonFor('Stale cheque', $m));
        $this->assertSame(10, R::bounceReasonFor('Account closed', $m)); // was Misc (9) before the master
        $this->assertNull(R::bounceReasonFor('No clearing reason on sheet', $m));
        $this->assertNull(R::bounceReasonFor('', $m));
    }

    public function test_rejected_cheque_found_with_rent_and_vat_rows()
    {
        $rent = $this->pdc();
        $vat  = $this->pdc(['pdc_type' => 3, 'pdc_amt' => 12.5]);
        $out = R::match([$this->row()], [$rent, $vat], ['27' => 3]);
        $this->assertSame('found', $out[0]['status']);
        $this->assertCount(1, $out[0]['groups']);
        $this->assertSame([$rent->id, $vat->id], array_map(function ($p) { return $p->id; }, $out[0]['groups'][0]));
    }

    public function test_same_cheque_number_other_bank_is_ignored_when_bank_mapped()
    {
        $out = R::match([$this->row()], [$this->pdc(['bank_id' => 9])], ['27' => 3]);
        $this->assertSame('not_found', $out[0]['status']);
    }

    public function test_unmapped_bank_matches_on_cheque_number_alone()
    {
        $out = R::match([$this->row(['bank_code' => '99'])], [$this->pdc(['bank_id' => 9])], ['27' => 3]);
        $this->assertSame('found', $out[0]['status']);
    }

    public function test_two_contracts_are_narrowed_by_cheque_month_else_choose()
    {
        $sep = $this->pdc(['tenant_contract_id' => 11, 'pdc_check_date' => '2026-09-15']);
        $oct = $this->pdc(['tenant_contract_id' => 12, 'pdc_check_date' => '2025-10-15']);
        $one = R::match([$this->row()], [$sep, $oct], ['27' => 3]);
        $this->assertSame('found', $one[0]['status']);
        $this->assertSame($sep->id, $one[0]['groups'][0][0]->id);

        $a = $this->pdc(['tenant_contract_id' => 11]);
        $b = $this->pdc(['tenant_contract_id' => 12]);
        $two = R::match([$this->row()], [$a, $b], ['27' => 3]);
        $this->assertSame('choose', $two[0]['status']);
        $this->assertCount(2, $two[0]['groups']);
    }

    /** Sheet 28-09-2026, cheque 02100063: same number on two tenants' contracts in the same month — the amount decides. */
    public function test_same_month_contracts_are_narrowed_by_the_sheet_amount()
    {
        $right = $this->pdc(['tenant_contract_id' => 21, 'pdc_amt' => 250.0, 'pdc_check_date' => '2026-09-10']);
        $other = $this->pdc(['tenant_contract_id' => 22, 'pdc_amt' => 400.0, 'pdc_check_date' => '2026-09-25']);
        $out = R::match([$this->row(['amount' => 250.0, 'cheque_date' => '2026-09-10'])], [$right, $other], ['27' => 3]);
        $this->assertSame('found', $out[0]['status']);
        $this->assertSame($right->id, $out[0]['groups'][0][0]->id);
    }

    public function test_rent_plus_vat_group_amount_counts_both_rows()
    {
        $rent = $this->pdc(['tenant_contract_id' => 31, 'pdc_amt' => 250.0]);
        $vat  = $this->pdc(['tenant_contract_id' => 31, 'pdc_type' => 3, 'pdc_amt' => 12.5]);
        $other = $this->pdc(['tenant_contract_id' => 32, 'pdc_amt' => 250.0]);
        $out = R::match([$this->row(['amount' => 262.5])], [$rent, $vat, $other], ['27' => 3]);
        $this->assertSame('found', $out[0]['status']);
        $this->assertCount(2, $out[0]['groups'][0]);
    }

    public function test_same_amount_on_both_contracts_still_needs_a_choice()
    {
        $a = $this->pdc(['tenant_contract_id' => 41]);
        $b = $this->pdc(['tenant_contract_id' => 42]);
        $out = R::match([$this->row()], [$a, $b], ['27' => 3]);
        $this->assertSame('choose', $out[0]['status']);
    }

    public function test_already_bounced_group()
    {
        $out = R::match([$this->row()], [$this->pdc(['pdc_cancel_reason' => 1])], ['27' => 3]);
        $this->assertSame('already_bounced', $out[0]['status']);
    }
}
