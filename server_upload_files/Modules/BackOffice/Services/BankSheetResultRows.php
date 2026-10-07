<?php

namespace Modules\BackOffice\Services;

use Carbon\Carbon;

/**
 * Labels and Excel rows for a bank-sheet posting result. The result page
 * (_bank_sheet_receipts partial) and the Excel download both read their
 * status / type wording from here so the two can never disagree.
 * Pure: no DB. Runs on PHP < 7.4 in production: no arrow functions.
 */
class BankSheetResultRows
{
    const RECEIPT_HEADINGS = ['#', 'Receipt No', 'Receipt Date', 'Tenant', 'Agreement No', 'Building / Unit',
                              'Cheque No', 'Cheque Bank', 'Type', 'Amount', 'Status'];
    const NOT_POSTED_HEADINGS = ['Sheet Row', 'Cheque No', 'Cheque Bank', 'Cheque Date', 'Amount', 'Status', 'Reason'];
    const PENDING_HEADINGS = ['#', 'Cheque No', 'Cheque Bank', 'Cheque Date', 'Amount', 'Status', 'Reason',
                              'Last Upload', 'Last Uploaded On', 'Times Uploaded'];

    private static $types = [0 => 'Rent', 1 => 'General', 2 => 'Deposit'];

    private static $rowStatuses = [
        'failed'          => 'Failed',
        'skipped'         => 'Not selected',
        'not_found'       => 'Not in PLMS',
        'amount_mismatch' => 'Amount mismatch',
        'date_mismatch'   => 'Date mismatch',
        'unknown_bank'    => 'Unknown bank',
        'redeposit_needed' => 'Redeposit needed',
        'already_posted'  => 'Already posted',
    ];

    /**
     * [css class, icon, label] for a receipt's current state.
     * approval_status: 1 not approved, 2/5 approval requested, 3 approved, 4 rejected, 6 posted to AX
     */
    public static function receiptStatus($r)
    {
        $a = (int) $r->approval_status;
        if ($r->deleted_at)                 return ['bsr-pill-off',  'fa-trash',        'Deleted'];
        if ((int) $r->receipt_status === 2) return ['bsr-pill-bad',  'fa-ban',          'Cancelled'];
        if ($a === 6)                       return ['bsr-pill-ok',   'fa-check-circle', 'Posted to AX'];
        if ($a === 3)                       return ['bsr-pill-ok',   'fa-check',        'Approved'];
        if ($a === 4)                       return ['bsr-pill-bad',  'fa-times-circle', 'Rejected'];
        if ($a === 2 || $a === 5)           return ['bsr-pill-warn', 'fa-clock-o',      'Approval requested'];
        return ['bsr-pill-warn', 'fa-clock-o', 'Not approved'];
    }

    public static function receiptType($r)
    {
        if ($r->is_vat) {
            return 'General (VAT)';
        }
        $type = (int) $r->receipt_type;
        return isset(self::$types[$type]) ? self::$types[$type] : '-';
    }

    /** Label for a not-posted sheet row's status code. */
    public static function rowStatus($status)
    {
        return isset(self::$rowStatuses[$status]) ? self::$rowStatuses[$status] : $status;
    }

    /** Cancelled and deleted receipts are left out of the total. */
    public static function countsInTotal($r)
    {
        return !$r->deleted_at && (int) $r->receipt_status !== 2;
    }

    public static function total($receipts)
    {
        $total = 0.0;
        foreach ($receipts as $r) {
            if (self::countsInTotal($r)) {
                $total += (float) $r->amount;
            }
        }
        return round($total, 3);
    }

    public static function date($d)
    {
        return $d ? Carbon::parse($d)->format('d/m/Y') : '-';
    }

    /**
     * "Receipts created" sheet: upload summary, a blank row, the column
     * headings, one row per receipt and a total row.
     * heading_row / total_row are 1-based Excel row numbers (total_row null when empty).
     */
    public static function receiptsSheet($upload, $receipts)
    {
        $rows = [
            ['Sheet', (string) $upload->original_name],
            ['Posted by', ($upload->username ?: '-') . ' on ' . Carbon::parse($upload->created_at)->format('d/m/Y H:i')],
            ['Deposit bank', $upload->bank_name ? $upload->bank_name . ' - ' . $upload->bank_code : '-'],
            ['Deposit date', self::date($upload->pdc_deposit_date)],
            [''], // spacer (an empty array would be dropped by the Excel writer)
            self::RECEIPT_HEADINGS,
        ];
        $headingRow = count($rows);

        $n = 0;
        foreach ($receipts as $r) {
            $n++;
            $status = self::receiptStatus($r)[2];
            if ($r->is_vat && $r->row_reason) {
                $status .= ' - ' . $r->row_reason;
            }
            $rows[] = [
                $n,
                (string) $r->receipt_no,
                self::date($r->receipt_date),
                $r->tenant_name ?: '-',
                $r->tenant_contract_no ?: '-',
                ($r->building_name ?: '-') . ($r->unit_no ? ' / ' . $r->unit_no : ''),
                (string) $r->sheet_cheque_no,
                $r->sheet_bank_name ?: '-',
                self::receiptType($r),
                round((float) $r->amount, 3),
                $status,
            ];
        }

        $totalRow = null;
        if ($n === 0) {
            $rows[] = ['No receipts were created.'];
        } else {
            $rows[] = ['Total (' . $n . ' receipts)', '', '', '', '', '', '', '', '', self::total($receipts), ''];
            $totalRow = count($rows);
        }

        return ['rows' => $rows, 'heading_row' => $headingRow, 'total_row' => $totalRow, 'amount_column' => 'J'];
    }

    /** "Not posted" sheet: headings then one row per cheque the run did not post. */
    public static function notPostedSheet($notPosted)
    {
        $rows = [self::NOT_POSTED_HEADINGS];
        foreach ($notPosted as $x) {
            $rows[] = [
                (int) $x->sheet_row_no,
                (string) $x->cheque_no,
                $x->bank_name ?: '-',
                self::date($x->cheque_date),
                round((float) $x->amount, 3),
                self::rowStatus($x->status),
                $x->reason ?: '-',
            ];
        }
        return ['rows' => $rows, 'heading_row' => 1, 'total_row' => null, 'amount_column' => 'E'];
    }

    /** "Pending cheques" sheet: headings, one row per cheque still waiting, then the total. */
    public static function pendingSheet(array $pending)
    {
        $rows = [self::PENDING_HEADINGS];
        $total = 0.0;
        foreach ($pending as $i => $x) {
            $rows[] = [
                $i + 1,
                (string) $x->cheque_no,
                $x->bank_name ?: '-',
                self::date($x->cheque_date),
                round((float) $x->amount, 3),
                self::rowStatus($x->status),
                $x->reason ?: '-',
                '#' . $x->upload_id,
                self::date($x->uploaded_at),
                (int) $x->times,
            ];
            $total += (float) $x->amount;
        }
        $rows[] = ['', '', '', 'Total', round($total, 3), '', '', '', '', ''];
        return ['rows' => $rows, 'heading_row' => 1, 'total_row' => count($rows), 'amount_column' => 'E'];
    }
}
