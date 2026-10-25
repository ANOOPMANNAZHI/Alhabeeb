<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use Modules\BackOffice\Services\BankSheetReader;

class BankSheetReaderTest extends TestCase
{
    /** @var string[] temp files to delete */
    private $files = [];

    protected function tearDown(): void
    {
        foreach ($this->files as $f) {
            @unlink($f);
        }
    }

    /**
     * Writes an .xls shaped like the bank's GRID_EXPORT: a few title rows,
     * the header on row 9, data from row 10. $rows are keyed by header title.
     */
    private function sheet(array $rows, $headerRow = 9)
    {
        $headers = ['Status', 'Cheque Number', 'Pay Bank', 'Pay Account', 'Created on', 'Amount', 'Cheque Date', 'Posting Date', 'Onus Reason', 'ECC Reason', 'Resolution'];
        $ss = new Spreadsheet();
        $ws = $ss->getActiveSheet();
        $ws->setTitle('GRID_EXPORT');
        $ws->setCellValue('B1', 'Cheques Out');
        $ws->setCellValue('B3', 'RDC');
        foreach ($headers as $i => $h) {
            $ws->setCellValue([$i + 2, $headerRow], $h); // start at column B like the bank does
        }
        $r = $headerRow + 1;
        foreach ($rows as $row) {
            foreach ($headers as $i => $h) {
                if (array_key_exists($h, $row)) {
                    if (is_int($row[$h]) || is_float($row[$h])) {
                        $ws->setCellValue([$i + 2, $r], $row[$h]); // a real numeric cell
                    } else {
                        $ws->setCellValueExplicit([$i + 2, $r], $row[$h], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                    }
                }
            }
            $r++;
        }
        $path = tempnam(sys_get_temp_dir(), 'bs') . '.xls';
        (new Xls($ss))->save($path);
        $this->files[] = $path;
        return $path;
    }

    private function acceptedRow(array $overrides = [])
    {
        return array_merge([
            'Status'        => 'Replied',
            'Cheque Number' => '80281048',
            'Pay Bank'      => '27 - Bank Of Muscat',
            'Pay Account'   => '00045288830024',
            'Created on'    => '15/09/2026 09:47:09 AM',
            'Amount'        => 'OMR 230.000',
            'Cheque Date'   => '15/09/2026',
            'Posting Date'  => '15/09/2026',
            'Onus Reason'   => '0 - 000- ACCEPTED',
            'ECC Reason'    => '',
            'Resolution'    => 'Accepted',
        ], $overrides);
    }

    public function test_reads_data_rows_below_the_header_and_parses_the_fields()
    {
        $rows = (new BankSheetReader())->read($this->sheet([$this->acceptedRow()]));

        $this->assertCount(1, $rows);
        $this->assertSame(10, $rows[0]['row_no']);
        $this->assertSame('80281048', $rows[0]['cheque_no']);
        $this->assertSame('27', $rows[0]['bank_code']);
        $this->assertSame('27 - Bank Of Muscat', $rows[0]['bank_name']);
        $this->assertSame(230.0, $rows[0]['amount']);
        $this->assertSame('2026-09-15', $rows[0]['cheque_date']);
        $this->assertSame('2026-09-15', $rows[0]['created_on']);
        $this->assertTrue($rows[0]['accepted']);
        $this->assertNull($rows[0]['reject_reason']);
    }

    public function test_pay_account_is_read_as_text_with_its_leading_zeros()
    {
        $rows = (new BankSheetReader())->read($this->sheet([$this->acceptedRow()]));

        $this->assertSame('00045288830024', $rows[0]['account_no']);
    }

    public function test_numeric_pay_account_cell_keeps_every_digit()
    {
        $rows = (new BankSheetReader())->read($this->sheet([$this->acceptedRow(['Pay Account' => 45288830024])]));

        $this->assertSame('45288830024', $rows[0]['account_no']);
    }

    public function test_blank_pay_account_is_null()
    {
        $rows = (new BankSheetReader())->read($this->sheet([$this->acceptedRow(['Pay Account' => ' '])]));

        $this->assertNull($rows[0]['account_no']);
    }

    public function test_batch_date_is_the_most_common_created_on_of_accepted_rows()
    {
        $rows = (new BankSheetReader())->read($this->sheet([
            $this->acceptedRow(['Created on' => '16/09/2026 08:00:00 AM']),
            $this->acceptedRow(['Created on' => '15/09/2026 09:47:09 AM']),
            $this->acceptedRow(['Created on' => '15/09/2026 10:12:00 AM']),
            $this->acceptedRow(['Created on' => '17/09/2026 09:00:00 AM', 'Resolution' => 'Rejected', 'ECC Reason' => '1 - 001-Insufficient Fund']),
        ]));

        $this->assertSame('2026-09-15', BankSheetReader::batchDate($rows));
    }

    public function test_batch_date_is_null_when_no_row_has_a_created_on()
    {
        $rows = (new BankSheetReader())->read($this->sheet([$this->acceptedRow(['Created on' => ''])]));

        $this->assertNull(BankSheetReader::batchDate($rows));
    }

    public function test_header_is_located_by_title_not_by_fixed_row()
    {
        $rows = (new BankSheetReader())->read($this->sheet([$this->acceptedRow()], 4));

        $this->assertCount(1, $rows);
        $this->assertSame(5, $rows[0]['row_no']);
    }

    public function test_amount_with_thousands_separator_is_parsed()
    {
        $rows = (new BankSheetReader())->read($this->sheet([$this->acceptedRow(['Amount' => 'OMR 1,250.500'])]));

        $this->assertSame(1250.5, $rows[0]['amount']);
    }

    public function test_accepted_when_only_ecc_reason_is_filled()
    {
        $rows = (new BankSheetReader())->read($this->sheet([
            $this->acceptedRow(['Onus Reason' => '', 'ECC Reason' => '0 - 000- ACCEPTED']),
        ]));

        $this->assertTrue($rows[0]['accepted']);
    }

    public function test_accepted_when_both_reasons_are_accepted()
    {
        $rows = (new BankSheetReader())->read($this->sheet([
            $this->acceptedRow(['Onus Reason' => '0 - 000- ACCEPTED', 'ECC Reason' => '0 - 000- ACCEPTED']),
        ]));

        $this->assertTrue($rows[0]['accepted']);
    }

    public function test_rejected_row_carries_the_reason_from_whichever_column_has_it()
    {
        $rows = (new BankSheetReader())->read($this->sheet([
            $this->acceptedRow(['Onus Reason' => '', 'ECC Reason' => '1 - 001-Insufficient Fund', 'Resolution' => 'Rejected']),
            $this->acceptedRow(['Onus Reason' => '11 - 011- Signature Required', 'ECC Reason' => '', 'Resolution' => 'Rejected']),
        ]));

        $this->assertFalse($rows[0]['accepted']);
        $this->assertSame('1 - 001-Insufficient Fund', $rows[0]['reject_reason']);
        $this->assertFalse($rows[1]['accepted']);
        $this->assertSame('11 - 011- Signature Required', $rows[1]['reject_reason']);
    }

    public function test_resolution_accepted_but_reason_not_accepted_is_not_accepted()
    {
        $rows = (new BankSheetReader())->read($this->sheet([
            $this->acceptedRow(['Onus Reason' => '', 'ECC Reason' => '22 - 022-NSF/ Eligible for PPS', 'Resolution' => 'Accepted']),
        ]));

        $this->assertFalse($rows[0]['accepted']);
        $this->assertSame('22 - 022-NSF/ Eligible for PPS', $rows[0]['reject_reason']);
    }

    public function test_resolution_accepted_with_both_reasons_blank_is_not_accepted()
    {
        $rows = (new BankSheetReader())->read($this->sheet([
            $this->acceptedRow(['Onus Reason' => '', 'ECC Reason' => '', 'Resolution' => 'Accepted']),
        ]));

        $this->assertFalse($rows[0]['accepted']);
        $this->assertSame('No clearing reason on sheet', $rows[0]['reject_reason']);
    }

    public function test_reason_and_resolution_comparison_ignores_case_and_spacing()
    {
        $rows = (new BankSheetReader())->read($this->sheet([
            $this->acceptedRow(['Onus Reason' => ' 0 - 000-  accepted ', 'Resolution' => 'ACCEPTED ']),
        ]));

        $this->assertTrue($rows[0]['accepted']);
    }

    public function test_rows_without_a_cheque_number_are_skipped()
    {
        $rows = (new BankSheetReader())->read($this->sheet([
            $this->acceptedRow(),
            ['Status' => 'Total', 'Amount' => 'OMR 230.000'],
            $this->acceptedRow(['Cheque Number' => '123']),
        ]));

        $this->assertSame(['80281048', '123'], array_column($rows, 'cheque_no'));
    }

    public function test_missing_header_row_throws()
    {
        $ss = new Spreadsheet();
        $ss->getActiveSheet()->setCellValue('A1', 'nothing here');
        $path = tempnam(sys_get_temp_dir(), 'bs') . '.xls';
        (new Xls($ss))->save($path);
        $this->files[] = $path;

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cheque Number');
        (new BankSheetReader())->read($path);
    }
}
