<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Modules\BackOffice\Services\BankSheetPdcMatcher;

class BankSheetPdcMatcherTest extends TestCase
{
    /** A parsed, accepted bank-sheet row. */
    private function sheetRow(array $overrides = [])
    {
        return array_merge([
            'row_no'      => 10,
            'cheque_no'   => '80281048',
            'bank_code'   => '27',
            'bank_name'   => '27 - Bank Of Muscat',
            'amount'      => 230.0,
            'cheque_date' => '2026-09-15',
            'accepted'    => true,
            'reject_reason' => null,
        ], $overrides);
    }

    /** An unposted PDC row as pulled from the pdc table. */
    private function pdc(array $overrides = [])
    {
        static $id = 1000;
        $id++;
        return (object) array_merge([
            'id'                 => $id,
            'pdc_check_no'       => '80281048',
            'bank_id'            => 3,
            'pdc_amt'            => 230.0,
            'pdc_check_date'     => '2026-09-01',
            'tenant_contract_id' => 78539,
            'tenant_id'          => 500,
            'tenant_name'        => 'Ahmed',
            'tenant_contract_no' => 'TC-1',
            'building_name'      => 'Bldg A',
            'unit_no'            => '12',
        ], $overrides);
    }

    private function matcher()
    {
        // bank sheet code => pdc.bank_id
        return new BankSheetPdcMatcher(['27' => 3, '30' => 17, '20' => 5]);
    }

    private function vatPdc(array $overrides = [])
    {
        return $this->pdc(array_merge(['pdc_type' => 3, 'pdc_amt' => 62.5], $overrides));
    }

    public function test_single_row_with_same_cheque_bank_amount_and_month_is_matched()
    {
        $out = $this->matcher()->match([$this->sheetRow()], [$this->pdc()]);

        $this->assertCount(1, $out);
        $this->assertSame(BankSheetPdcMatcher::MATCHED, $out[0]['status']);
        $this->assertSame([1001], array_column($out[0]['pdcs'], 'id'));
    }

    public function test_cheque_with_no_pdc_of_that_number_and_bank_is_not_found()
    {
        $out = $this->matcher()->match([$this->sheetRow()], [$this->pdc(['pdc_check_no' => '99999'])]);

        $this->assertSame(BankSheetPdcMatcher::NOT_FOUND, $out[0]['status']);
        $this->assertSame([], $out[0]['pdcs']);
    }

    public function test_leading_zeros_on_sheet_cheque_no_are_ignored()
    {
        $out = $this->matcher()->match(
            [$this->sheetRow(['cheque_no' => '02520341', 'amount' => 290.0])],
            [$this->pdc(['pdc_check_no' => '2520341', 'pdc_amt' => 290.0])]
        );

        $this->assertSame(BankSheetPdcMatcher::MATCHED, $out[0]['status']);
    }

    public function test_same_cheque_no_on_a_different_bank_does_not_match()
    {
        $out = $this->matcher()->match([$this->sheetRow(['bank_code' => '30'])], [$this->pdc(['bank_id' => 3])]);

        $this->assertSame(BankSheetPdcMatcher::NOT_FOUND, $out[0]['status']);
    }

    public function test_unknown_bank_code_is_reported_not_guessed()
    {
        $out = $this->matcher()->match([$this->sheetRow(['bank_code' => '77', 'bank_name' => '77 - Some Bank'])], [$this->pdc()]);

        $this->assertSame(BankSheetPdcMatcher::UNKNOWN_BANK, $out[0]['status']);
        $this->assertStringContainsString('77 - Some Bank', $out[0]['reason']);
    }

    public function test_pdc_dated_in_another_month_is_a_date_mismatch()
    {
        $out = $this->matcher()->match(
            [$this->sheetRow(['cheque_date' => '2026-09-30'])],
            [$this->pdc(['pdc_check_date' => '2026-10-01'])]
        );

        $this->assertSame(BankSheetPdcMatcher::DATE_MISMATCH, $out[0]['status']);
        $this->assertStringContainsString('2026-10-01', $out[0]['reason']);
    }

    public function test_pdc_anywhere_in_the_cheque_month_matches()
    {
        $out = $this->matcher()->match(
            [$this->sheetRow(['cheque_date' => '2026-02-15'])],
            [$this->pdc(['pdc_check_date' => '2026-02-28'])]
        );

        $this->assertSame(BankSheetPdcMatcher::MATCHED, $out[0]['status']);
    }

    public function test_split_cheque_matches_when_group_amounts_sum_to_sheet_amount()
    {
        $pdcs = [
            $this->pdc(['pdc_check_no' => '79518811', 'bank_id' => 5, 'pdc_amt' => 200, 'tenant_contract_id' => 76523]),
            $this->pdc(['pdc_check_no' => '79518811', 'bank_id' => 5, 'pdc_amt' => 200, 'tenant_contract_id' => 76524]),
            $this->pdc(['pdc_check_no' => '79518811', 'bank_id' => 5, 'pdc_amt' => 200, 'tenant_contract_id' => 76525]),
            $this->pdc(['pdc_check_no' => '79518811', 'bank_id' => 5, 'pdc_amt' => 200, 'tenant_contract_id' => 76526]),
        ];
        $out = $this->matcher()->match([$this->sheetRow(['cheque_no' => '79518811', 'bank_code' => '20', 'amount' => 800.0])], $pdcs);

        $this->assertSame(BankSheetPdcMatcher::MATCHED, $out[0]['status']);
        $this->assertCount(4, $out[0]['pdcs']);
    }

    public function test_split_cheque_whose_group_does_not_sum_to_sheet_amount_is_an_amount_mismatch()
    {
        $pdcs = [
            $this->pdc(['pdc_check_no' => '79518811', 'bank_id' => 5, 'pdc_amt' => 200, 'tenant_contract_id' => 76523]),
            $this->pdc(['pdc_check_no' => '79518811', 'bank_id' => 5, 'pdc_amt' => 200, 'tenant_contract_id' => 76524]),
            $this->pdc(['pdc_check_no' => '79518811', 'bank_id' => 5, 'pdc_amt' => 200, 'tenant_contract_id' => 76525]),
        ];
        $out = $this->matcher()->match([$this->sheetRow(['cheque_no' => '79518811', 'bank_code' => '20', 'amount' => 800.0])], $pdcs);

        $this->assertSame(BankSheetPdcMatcher::AMOUNT_MISMATCH, $out[0]['status']);
        $this->assertStringContainsString('600', $out[0]['reason']);
        $this->assertStringContainsString('800', $out[0]['reason']);
    }

    public function test_coincidental_duplicate_number_resolves_by_amount()
    {
        $pdcs = [
            $this->pdc(['pdc_check_no' => '1000009', 'bank_id' => 17, 'pdc_amt' => 295, 'tenant_contract_id' => 76670, 'tenant_id' => 501]),
            $this->pdc(['pdc_check_no' => '1000009', 'bank_id' => 17, 'pdc_amt' => 250, 'tenant_contract_id' => 77607, 'tenant_id' => 502]),
            $this->pdc(['pdc_check_no' => '1000009', 'bank_id' => 17, 'pdc_amt' => 150, 'tenant_contract_id' => 77542, 'tenant_id' => 503]),
        ];
        $out = $this->matcher()->match([$this->sheetRow(['cheque_no' => '1000009', 'bank_code' => '30', 'amount' => 295.0])], $pdcs);

        $this->assertSame(BankSheetPdcMatcher::MATCHED, $out[0]['status']);
        $this->assertSame([76670], array_column($out[0]['pdcs'], 'tenant_contract_id'));
    }

    public function test_two_tenants_with_same_number_bank_amount_and_month_is_ambiguous_with_candidates()
    {
        $pdcs = [
            $this->pdc(['pdc_check_no' => '1000009', 'bank_id' => 17, 'pdc_amt' => 250, 'tenant_contract_id' => 77607, 'tenant_id' => 501, 'tenant_name' => 'Salim']),
            $this->pdc(['pdc_check_no' => '1000009', 'bank_id' => 17, 'pdc_amt' => 250, 'tenant_contract_id' => 77542, 'tenant_id' => 502, 'tenant_name' => 'Fatma']),
        ];
        $out = $this->matcher()->match([$this->sheetRow(['cheque_no' => '1000009', 'bank_code' => '30', 'amount' => 250.0])], $pdcs);

        $this->assertSame(BankSheetPdcMatcher::AMBIGUOUS, $out[0]['status']);
        $this->assertSame([], $out[0]['pdcs']);
        $this->assertCount(2, $out[0]['candidates']);
        // each candidate is a group (list of pdc rows) the user can pick
        $this->assertSame([77607], array_column($out[0]['candidates'][0], 'tenant_contract_id'));
        $this->assertSame([77542], array_column($out[0]['candidates'][1], 'tenant_contract_id'));
    }

    public function test_ambiguity_between_a_split_group_and_a_single_row_of_same_total()
    {
        // Tenant A: two rows 100+150 on one cheque; Tenant B: one row 250 on same number/bank/month
        $pdcs = [
            $this->pdc(['pdc_check_no' => '5555', 'bank_id' => 17, 'pdc_amt' => 100, 'tenant_contract_id' => 1, 'tenant_id' => 501, 'tenant_name' => 'A']),
            $this->pdc(['pdc_check_no' => '5555', 'bank_id' => 17, 'pdc_amt' => 150, 'tenant_contract_id' => 2, 'tenant_id' => 501, 'tenant_name' => 'A']),
            $this->pdc(['pdc_check_no' => '5555', 'bank_id' => 17, 'pdc_amt' => 250, 'tenant_contract_id' => 3, 'tenant_id' => 502, 'tenant_name' => 'B']),
        ];
        $out = $this->matcher()->match([$this->sheetRow(['cheque_no' => '5555', 'bank_code' => '30', 'amount' => 250.0])], $pdcs);

        $this->assertSame(BankSheetPdcMatcher::AMBIGUOUS, $out[0]['status']);
        $this->assertCount(2, $out[0]['candidates']);
    }

    public function test_rejected_sheet_row_is_reported_and_never_matched()
    {
        $out = $this->matcher()->match(
            [$this->sheetRow(['accepted' => false, 'reject_reason' => '1 - 001-Insufficient Fund'])],
            [$this->pdc()]
        );

        $this->assertSame(BankSheetPdcMatcher::REJECTED, $out[0]['status']);
        $this->assertSame([], $out[0]['pdcs']);
        $this->assertSame('1 - 001-Insufficient Fund', $out[0]['reason']);
    }

    public function test_a_pdc_row_is_only_used_once_across_the_sheet()
    {
        // The bank sheet lists the same cheque twice (re-presented); only one PLMS row exists.
        $out = $this->matcher()->match(
            [$this->sheetRow(['row_no' => 10]), $this->sheetRow(['row_no' => 11])],
            [$this->pdc()]
        );

        $this->assertSame(BankSheetPdcMatcher::MATCHED, $out[0]['status']);
        $this->assertSame(BankSheetPdcMatcher::NOT_FOUND, $out[1]['status']);
    }

    public function test_a_sheet_bank_code_may_map_to_several_plms_banks()
    {
        // Meethaq (34) cheques are entered in PLMS sometimes as Meethaq (31), sometimes as Bank Muscat (3)
        $matcher = new BankSheetPdcMatcher(['34' => [31, 3]]);
        $out = $matcher->match(
            [$this->sheetRow(['bank_code' => '34'])],
            [$this->pdc(['bank_id' => 3])]
        );

        $this->assertSame(BankSheetPdcMatcher::MATCHED, $out[0]['status']);
    }

    public function test_amount_comparison_tolerates_float_noise()
    {
        $out = $this->matcher()->match(
            [$this->sheetRow(['amount' => 0.3])],
            [$this->pdc(['pdc_amt' => 0.1]), $this->pdc(['pdc_amt' => 0.2])]
        );

        $this->assertSame(BankSheetPdcMatcher::MATCHED, $out[0]['status']);
    }

    public function test_rent_plus_vat_pdc_summing_to_bank_amount_is_matched_with_both_rows()
    {
        $rent = $this->pdc(['pdc_amt' => 1250.0, 'pdc_type' => 1]);
        $vat  = $this->vatPdc();
        $out  = $this->matcher()->match([$this->sheetRow(['amount' => 1312.5])], [$rent, $vat]);

        $this->assertSame(BankSheetPdcMatcher::MATCHED, $out[0]['status']);
        $this->assertSame([$rent->id, $vat->id], array_column($out[0]['pdcs'], 'id'));
    }

    public function test_rent_alone_equal_to_bank_amount_matches_without_the_vat_pdc()
    {
        $rent = $this->pdc(['pdc_amt' => 1250.0]);
        $out  = $this->matcher()->match([$this->sheetRow(['amount' => 1250.0])], [$rent, $this->vatPdc()]);

        $this->assertSame(BankSheetPdcMatcher::MATCHED, $out[0]['status']);
        $this->assertSame([$rent->id], array_column($out[0]['pdcs'], 'id'));
    }

    public function test_comprehensive_rent_with_extra_near_five_percent_is_vat_check()
    {
        foreach ([157.0, 157.5, 158.0, 156.5, 158.5] as $bank) {
            $rent = $this->pdc(['pdc_amt' => 150.0, 'management_id' => 1]);
            $out  = $this->matcher()->match([$this->sheetRow(['amount' => $bank])], [$rent]);

            $this->assertSame(BankSheetPdcMatcher::VAT_CHECK, $out[0]['status'], "bank $bank");
            $this->assertSame([$rent->id], array_column($out[0]['pdcs'], 'id'));
            $this->assertEqualsWithDelta($bank - 150.0, $out[0]['vat_amount'], 0.0001);
            $this->assertEqualsWithDelta(7.5, $out[0]['expected_vat'], 0.0001);
        }
    }

    public function test_extra_outside_tolerance_is_an_amount_mismatch()
    {
        foreach ([159.0, 156.0, 150.5] as $bank) {
            $out = $this->matcher()->match(
                [$this->sheetRow(['amount' => $bank])],
                [$this->pdc(['pdc_amt' => 150.0, 'management_id' => 1])]
            );
            $this->assertSame(BankSheetPdcMatcher::AMOUNT_MISMATCH, $out[0]['status'], "bank $bank");
        }
    }

    public function test_non_comprehensive_building_never_gets_vat_by_difference()
    {
        $out = $this->matcher()->match(
            [$this->sheetRow(['amount' => 157.5])],
            [$this->pdc(['pdc_amt' => 150.0, 'management_id' => 2])]
        );
        $this->assertSame(BankSheetPdcMatcher::AMOUNT_MISMATCH, $out[0]['status']);
    }

    public function test_vat_check_carries_the_agreements_vat_pdc_when_its_amount_is_off()
    {
        $rent = $this->pdc(['pdc_amt' => 1250.0, 'management_id' => 1]);
        $vat  = $this->vatPdc(['pdc_amt' => 60.0, 'management_id' => 1]);
        $out  = $this->matcher()->match([$this->sheetRow(['amount' => 1312.5])], [$rent, $vat]);

        $this->assertSame(BankSheetPdcMatcher::VAT_CHECK, $out[0]['status']);
        $this->assertSame([$rent->id, $vat->id], array_column($out[0]['pdcs'], 'id'));
        $this->assertEqualsWithDelta(62.5, $out[0]['vat_amount'], 0.0001);
    }

    public function test_a_row_matching_only_a_vat_pdc_is_not_found()
    {
        $out = $this->matcher()->match([$this->sheetRow(['amount' => 62.5])], [$this->vatPdc()]);
        $this->assertSame(BankSheetPdcMatcher::NOT_FOUND, $out[0]['status']);
    }

    public function test_vat_check_needs_a_single_agreement()
    {
        $a = $this->pdc(['pdc_amt' => 100.0, 'management_id' => 1, 'tenant_contract_id' => 1]);
        $b = $this->pdc(['pdc_amt' => 50.0, 'management_id' => 1, 'tenant_contract_id' => 2]);
        $out = $this->matcher()->match([$this->sheetRow(['amount' => 157.5])], [$a, $b]);
        $this->assertSame(BankSheetPdcMatcher::AMOUNT_MISMATCH, $out[0]['status']);
    }

    public function test_non_vat_results_have_null_vat_fields()
    {
        $out = $this->matcher()->match([$this->sheetRow()], [$this->pdc()]);
        $this->assertNull($out[0]['vat_amount']);
        $this->assertNull($out[0]['expected_vat']);
    }

    public function test_amount_mismatch_carries_the_pdcs_it_compared_as_related()
    {
        $pdc = $this->pdc(['pdc_amt' => 200.0]);
        $out = $this->matcher()->match([$this->sheetRow(['amount' => 230.0])], [$pdc]);

        $this->assertSame(BankSheetPdcMatcher::AMOUNT_MISMATCH, $out[0]['status']);
        $this->assertSame([], $out[0]['pdcs']);
        $this->assertSame([$pdc->id], array_column($out[0]['related'], 'id'));
        $this->assertSame(BankSheetPdcMatcher::RELATED_SAME, $out[0]['related_kind']);
    }

    public function test_date_mismatch_carries_the_other_month_pdcs_as_related()
    {
        $pdc = $this->pdc(['pdc_check_date' => '2026-08-15']);
        $out = $this->matcher()->match([$this->sheetRow()], [$pdc]);

        $this->assertSame(BankSheetPdcMatcher::DATE_MISMATCH, $out[0]['status']);
        $this->assertSame([$pdc->id], array_column($out[0]['related'], 'id'));
        $this->assertSame(BankSheetPdcMatcher::RELATED_SAME, $out[0]['related_kind']);
    }

    public function test_not_found_hints_at_the_same_cheque_on_another_bank()
    {
        $elsewhere = $this->pdc(['bank_id' => 17]); // sheet bank 27 maps to PLMS bank 3
        $out = $this->matcher()->match([$this->sheetRow()], [$elsewhere, $this->vatPdc(['bank_id' => 17])]);

        $this->assertSame(BankSheetPdcMatcher::NOT_FOUND, $out[0]['status']);
        $this->assertSame([], $out[0]['pdcs']);
        $this->assertSame([$elsewhere->id], array_column($out[0]['related'], 'id'), 'VAT PDCs are not hinted');
        $this->assertSame(BankSheetPdcMatcher::RELATED_OTHER_BANK, $out[0]['related_kind']);
    }

    public function test_unknown_bank_hints_at_the_same_cheque_on_any_bank()
    {
        $pdc = $this->pdc();
        $out = $this->matcher()->match([$this->sheetRow(['bank_code' => '99', 'bank_name' => '99 - New Bank'])], [$pdc]);

        $this->assertSame(BankSheetPdcMatcher::UNKNOWN_BANK, $out[0]['status']);
        $this->assertSame([$pdc->id], array_column($out[0]['related'], 'id'));
        $this->assertSame(BankSheetPdcMatcher::RELATED_OTHER_BANK, $out[0]['related_kind']);
    }

    public function test_not_found_with_no_such_cheque_anywhere_has_no_related()
    {
        $out = $this->matcher()->match([$this->sheetRow()], [$this->pdc(['pdc_check_no' => '99999'])]);

        $this->assertSame([], $out[0]['related']);
        $this->assertNull($out[0]['related_kind']);
    }

    public function test_related_hint_skips_pdcs_already_matched_by_an_earlier_row()
    {
        $pdc = $this->pdc(['bank_id' => 17]);
        $out = $this->matcher()->match(
            [$this->sheetRow(['row_no' => 1, 'bank_code' => '30']), $this->sheetRow(['row_no' => 2])],
            [$pdc]
        );

        $this->assertSame(BankSheetPdcMatcher::MATCHED, $out[0]['status']);
        $this->assertSame([], $out[1]['related']);
    }

    public function test_matched_rows_have_no_related()
    {
        $out = $this->matcher()->match([$this->sheetRow()], [$this->pdc()]);
        $this->assertSame([], $out[0]['related']);
        $this->assertNull($out[0]['related_kind']);
    }
}
