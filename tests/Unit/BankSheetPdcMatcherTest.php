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

    public function test_amount_comparison_tolerates_float_noise()
    {
        $out = $this->matcher()->match(
            [$this->sheetRow(['amount' => 0.3])],
            [$this->pdc(['pdc_amt' => 0.1]), $this->pdc(['pdc_amt' => 0.2])]
        );

        $this->assertSame(BankSheetPdcMatcher::MATCHED, $out[0]['status']);
    }
}
