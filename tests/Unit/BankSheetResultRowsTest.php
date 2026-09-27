<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Modules\BackOffice\Services\BankSheetResultRows;

class BankSheetResultRowsTest extends TestCase
{
    /** A receipt row as PdcBankSheetController::receiptRows() returns it. */
    private function receipt(array $overrides = [])
    {
        return (object) array_merge([
            'upload_id'          => 7,
            'sheet_row_no'       => 3,
            'sheet_cheque_no'    => '80281048',
            'sheet_bank_name'    => '27 - Bank Of Muscat',
            'is_vat'             => false,
            'row_reason'         => null,
            'receipt_id'         => 501,
            'receipt_no'         => 'RR-0001',
            'receipt_date'       => '2026-09-20',
            'amount'             => '230.500',
            'receipt_type'       => 0,
            'receipt_status'     => 1,
            'approval_status'    => 1,
            'deleted_at'         => null,
            'tenant_contract_no' => 'TC-100',
            'tenant_name'        => 'Ali',
            'building_name'      => 'Teeb Complex',
            'unit_no'            => 'S 03',
        ], $overrides);
    }

    private function upload()
    {
        return (object) [
            'id'               => 7,
            'original_name'    => 'Cheque.xls',
            'username'         => 'anoop',
            'created_at'       => '2026-09-20 16:30:00',
            'bank_name'        => 'Bank Muscat',
            'bank_code'        => 'BMI',
            'pdc_deposit_date' => '2026-09-19',
            'rows_posted'      => 2,
        ];
    }

    public function test_receipt_status_labels()
    {
        $this->assertSame('Not approved', BankSheetResultRows::receiptStatus($this->receipt())[2]);
        $this->assertSame('Approval requested', BankSheetResultRows::receiptStatus($this->receipt(['approval_status' => 2]))[2]);
        $this->assertSame('Approval requested', BankSheetResultRows::receiptStatus($this->receipt(['approval_status' => 5]))[2]);
        $this->assertSame('Approved', BankSheetResultRows::receiptStatus($this->receipt(['approval_status' => 3]))[2]);
        $this->assertSame('Rejected', BankSheetResultRows::receiptStatus($this->receipt(['approval_status' => 4]))[2]);
        $this->assertSame('Posted to AX', BankSheetResultRows::receiptStatus($this->receipt(['approval_status' => 6]))[2]);
        $this->assertSame('Cancelled', BankSheetResultRows::receiptStatus($this->receipt(['receipt_status' => 2, 'approval_status' => 6]))[2]);
        $this->assertSame('Deleted', BankSheetResultRows::receiptStatus($this->receipt(['deleted_at' => '2026-09-21', 'receipt_status' => 2]))[2]);
    }

    public function test_receipt_type_labels()
    {
        $this->assertSame('Rent', BankSheetResultRows::receiptType($this->receipt()));
        $this->assertSame('Deposit', BankSheetResultRows::receiptType($this->receipt(['receipt_type' => 2])));
        $this->assertSame('General (VAT)', BankSheetResultRows::receiptType($this->receipt(['receipt_type' => 1, 'is_vat' => true])));
        $this->assertSame('-', BankSheetResultRows::receiptType($this->receipt(['receipt_type' => 9])));
    }

    public function test_row_status_labels()
    {
        $this->assertSame('Amount mismatch', BankSheetResultRows::rowStatus('amount_mismatch'));
        $this->assertSame('Not selected', BankSheetResultRows::rowStatus('skipped'));
        $this->assertSame('something_new', BankSheetResultRows::rowStatus('something_new'));
    }

    public function test_total_skips_cancelled_and_deleted()
    {
        $receipts = [
            $this->receipt(['amount' => '100.000']),
            $this->receipt(['amount' => '50.250']),
            $this->receipt(['amount' => '999', 'receipt_status' => 2]),
            $this->receipt(['amount' => '888', 'deleted_at' => '2026-09-21']),
        ];
        $this->assertSame(150.25, BankSheetResultRows::total($receipts));
    }

    public function test_receipts_sheet_has_summary_headings_rows_and_total()
    {
        $sheet = BankSheetResultRows::receiptsSheet($this->upload(), [
            $this->receipt(),
            $this->receipt(['receipt_no' => 'GR-0002', 'receipt_type' => 1, 'is_vat' => true, 'amount' => '11.525',
                            'row_reason' => 'VAT receipt already exists: GR-0002', 'unit_no' => null]),
        ]);

        $rows = $sheet['rows'];
        $this->assertSame(['Sheet', 'Cheque.xls'], $rows[0]);
        $this->assertSame(['Posted by', 'anoop on 20/09/2026 16:30'], $rows[1]);
        $this->assertSame(['Deposit bank', 'Bank Muscat - BMI'], $rows[2]);
        $this->assertSame(['Deposit date', '19/09/2026'], $rows[3]);

        $h = $sheet['heading_row']; // 1-based Excel row of the column headings
        $this->assertSame(
            ['#', 'Receipt No', 'Receipt Date', 'Tenant', 'Agreement No', 'Building / Unit',
             'Cheque No', 'Cheque Bank', 'Type', 'Amount', 'Status'],
            $rows[$h - 1]
        );
        $this->assertSame(
            [1, 'RR-0001', '20/09/2026', 'Ali', 'TC-100', 'Teeb Complex / S 03',
             '80281048', '27 - Bank Of Muscat', 'Rent', 230.5, 'Not approved'],
            $rows[$h]
        );
        // VAT row: no unit, reason appended to the status
        $this->assertSame('Teeb Complex', $rows[$h + 1][5]);
        $this->assertSame('General (VAT)', $rows[$h + 1][8]);
        $this->assertSame(11.525, $rows[$h + 1][9]);
        $this->assertSame('Not approved - VAT receipt already exists: GR-0002', $rows[$h + 1][10]);

        $totalRow = $rows[count($rows) - 1];
        $this->assertSame('Total (2 receipts)', $totalRow[0]);
        $this->assertSame(242.025, $totalRow[9]);
        $this->assertSame(count($rows), $sheet['total_row']);
        $this->assertSame('J', $sheet['amount_column']);
    }

    public function test_receipts_sheet_with_no_receipts()
    {
        $sheet = BankSheetResultRows::receiptsSheet($this->upload(), []);
        $rows = $sheet['rows'];
        $this->assertSame(['No receipts were created.'], $rows[count($rows) - 1]);
        $this->assertNull($sheet['total_row']);
    }

    public function test_not_posted_sheet()
    {
        $sheet = BankSheetResultRows::notPostedSheet([
            (object) ['sheet_row_no' => 12, 'cheque_no' => '555', 'bank_name' => null,
                      'cheque_date' => '2026-09-15', 'amount' => '300', 'status' => 'not_found', 'reason' => null],
        ]);
        $this->assertSame(1, $sheet['heading_row']);
        $this->assertSame(['Sheet Row', 'Cheque No', 'Cheque Bank', 'Cheque Date', 'Amount', 'Status', 'Reason'], $sheet['rows'][0]);
        $this->assertSame([12, '555', '-', '15/09/2026', 300.0, 'Not in PLMS', '-'], $sheet['rows'][1]);
        $this->assertSame('E', $sheet['amount_column']);
        $this->assertNull($sheet['total_row']);
    }
}
