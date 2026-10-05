<?php

namespace Modules\BackOffice\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Reads the bank's cheque-clearing export (the "GRID_EXPORT" sheet the
 * RDC portal produces) into plain rows for BankSheetPdcMatcher. Columns are
 * found by header title, not letter, because the header sits below a few
 * title rows and the bank may add or move columns. A row counts as
 * accepted only when Resolution is "Accepted" and every filled reason
 * column (Onus Reason for on-us cheques, ECC Reason for clearing cheques)
 * reads "0 - 000- ACCEPTED". Runs on PHP < 7.4 in production: no arrow
 * functions.
 */
class BankSheetReader
{
    const COL_CHEQUE_NO  = 'Cheque Number';
    const COL_BANK       = 'Pay Bank';
    const COL_ACCOUNT    = 'Pay Account'; // drawer's account, zero-padded by the bank (00045288830024)
    const COL_AMOUNT     = 'Amount';
    const COL_DATE       = 'Cheque Date';
    const COL_CREATED    = 'Created on';
    const COL_ONUS       = 'Onus Reason';
    const COL_ECC        = 'ECC Reason';
    const COL_RESOLUTION = 'Resolution';

    const ACCEPTED_REASON     = '0 - 000- ACCEPTED';
    const ACCEPTED_RESOLUTION = 'Accepted';

    /**
     * @param string $path .xls / .xlsx path
     * @return array rows: row_no, cheque_no, bank_code, bank_name, account_no (raw text|null), amount (float),
     *               cheque_date (Y-m-d|null), created_on (Y-m-d|null), accepted (bool), reject_reason (string|null)
     * @throws \RuntimeException when the header row cannot be found
     */
    public function read($path)
    {
        $reader = IOFactory::createReaderForFile($path);
        $reader->setReadDataOnly(true);
        $ss = $reader->load($path);

        $sheet = $ss->getSheetByName('GRID_EXPORT');
        if ($sheet === null) {
            $sheet = $ss->getSheet(0);
        }
        $grid = $sheet->toArray(null, true, false, true);

        list($headerRowNo, $cols) = $this->locateHeader($grid);

        $rows = [];
        foreach ($grid as $rowNo => $cells) {
            if ($rowNo <= $headerRowNo) {
                continue;
            }
            $chequeNo = trim((string) $this->cell($cells, $cols, self::COL_CHEQUE_NO));
            if ($chequeNo === '') {
                continue;
            }
            $bankName = trim((string) $this->cell($cells, $cols, self::COL_BANK));
            list($accepted, $reason) = $this->acceptance(
                $this->cell($cells, $cols, self::COL_ONUS),
                $this->cell($cells, $cols, self::COL_ECC),
                $this->cell($cells, $cols, self::COL_RESOLUTION)
            );
            $rows[] = [
                'row_no'        => $rowNo,
                'cheque_no'     => $chequeNo,
                'bank_code'     => self::bankCode($bankName),
                'bank_name'     => $bankName,
                'account_no'    => $this->text($this->cell($cells, $cols, self::COL_ACCOUNT)),
                'amount'        => $this->amount($this->cell($cells, $cols, self::COL_AMOUNT)),
                'cheque_date'   => $this->date($this->cell($cells, $cols, self::COL_DATE)),
                'created_on'    => $this->date($this->cell($cells, $cols, self::COL_CREATED)),
                'accepted'      => $accepted,
                'reject_reason' => $reason,
            ];
        }

        return $rows;
    }

    /**
     * The day the batch was scanned, for the deposit date: the most common
     * "Created on" date among accepted rows (ties go to the earliest). Null
     * when no accepted row carries one.
     */
    public static function batchDate(array $rows)
    {
        $tally = [];
        foreach ($rows as $r) {
            if (!empty($r['accepted']) && !empty($r['created_on'])) {
                $d = $r['created_on'];
                $tally[$d] = isset($tally[$d]) ? $tally[$d] + 1 : 1;
            }
        }
        if (count($tally) === 0) {
            return null;
        }
        ksort($tally);
        $best = null;
        foreach ($tally as $d => $n) {
            if ($best === null || $n > $tally[$best]) {
                $best = $d;
            }
        }
        return $best;
    }

    /** @return array [header row number, [title => column letter]] */
    private function locateHeader(array $grid)
    {
        foreach ($grid as $rowNo => $cells) {
            $cols = [];
            foreach ($cells as $col => $value) {
                $title = trim((string) $value);
                if ($title !== '') {
                    $cols[$title] = $col;
                }
            }
            if (isset($cols[self::COL_CHEQUE_NO]) && isset($cols[self::COL_RESOLUTION])) {
                return [$rowNo, $cols];
            }
        }
        throw new \RuntimeException('Could not find the header row ("' . self::COL_CHEQUE_NO . '" / "' . self::COL_RESOLUTION . '") in the uploaded sheet');
    }

    /** Trimmed cell text, null when blank (or the column is absent). */
    private function text($value)
    {
        if (is_float($value) || is_int($value)) {
            // a numeric cell (export without text formatting): write all digits, never 4.5E+15
            $value = sprintf('%.0f', $value);
        }
        $value = trim((string) $value);
        return $value === '' ? null : $value;
    }

    private function cell(array $cells, array $cols, $title)
    {
        if (!isset($cols[$title])) {
            return null;
        }
        return isset($cells[$cols[$title]]) ? $cells[$cols[$title]] : null;
    }

    /** @return array [bool accepted, string|null reason] */
    private function acceptance($onus, $ecc, $resolution)
    {
        if ($this->norm($resolution) !== $this->norm(self::ACCEPTED_RESOLUTION)) {
            $reason = $this->firstFilled([$onus, $ecc]);
            return [false, $reason !== null ? $reason : trim((string) $resolution)];
        }
        $filled = [];
        foreach ([$onus, $ecc] as $r) {
            if (trim((string) $r) !== '') {
                $filled[] = $r;
            }
        }
        if (count($filled) === 0) {
            return [false, 'No clearing reason on sheet'];
        }
        foreach ($filled as $r) {
            if ($this->norm($r) !== $this->norm(self::ACCEPTED_REASON)) {
                return [false, trim((string) $r)];
            }
        }
        return [true, null];
    }

    private function firstFilled(array $values)
    {
        foreach ($values as $v) {
            if (trim((string) $v) !== '') {
                return trim((string) $v);
            }
        }
        return null;
    }

    /** Case- and whitespace-insensitive comparison key. */
    private function norm($value)
    {
        return strtolower(preg_replace('/\s+/', '', (string) $value));
    }

    /** "27 - Bank Of Muscat" => "27" */
    public static function bankCode($bankName)
    {
        if (preg_match('/^\s*(\d+)\s*-/', $bankName, $m)) {
            return $m[1];
        }
        return $bankName;
    }

    /** "OMR 1,250.500" => 1250.5 */
    private function amount($value)
    {
        if (is_numeric($value)) {
            return (float) $value;
        }
        $digits = preg_replace('/[^0-9.\-]/', '', (string) $value);
        return $digits === '' ? 0.0 : (float) $digits;
    }

    /** "15/09/2026" (d/m/Y, optionally with a time) => "2026-09-15" */
    private function date($value)
    {
        $value = trim((string) $value);
        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})/', $value, $m)) {
            return sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
        }
        if (is_numeric($value)) { // Excel serial date
            return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $value)->format('Y-m-d');
        }
        $ts = strtotime($value);
        return $ts === false ? null : date('Y-m-d', $ts);
    }
}
