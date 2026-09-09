<?php

namespace Modules\BackOffice\Services;

/**
 * Turns plain Landlord Invoice v2 facts into the AX AP Invoice Journal line
 * payloads consumed by Dynamics::LandlordInvoiceRegisterAxLineItemPushData().
 * Pure function: no models, no DB, no SOAP. See the AX posting spec.
 */
class LandlordInvoiceV2AxLineBuilder
{
    const TYPE_OTHER_DEDUCTIONS = 'other_deductions';

    /**
     * @param array $facts see LandlordInvoiceV2AxPoster::facts() for the key list
     * @return array[] ordered list of line payloads
     */
    public static function build(array $facts)
    {
        $reverse = ($facts['invoice_type'] === self::TYPE_OTHER_DEDUCTIONS);

        $lines = [];

        // 1. Vendor side for the grand total (credit on a tax invoice)
        $lines[] = self::line($facts, AX_VENDOR_CR, $facts['vendor_code'], 0.0, (float) $facts['grand_total'], $reverse);

        // 2. Expense ledger for the subtotal (debit on a tax invoice)
        $lines[] = self::line($facts, AX_LEDGER_DR, $facts['expense_account'], (float) $facts['subtotal'], 0.0, $reverse);

        // 3. VAT ledger, only when there is VAT
        if ((float) $facts['vat_total'] > 0) {
            $lines[] = self::line($facts, AX_LEDGER_DR, $facts['vat_account'], (float) $facts['vat_total'], 0.0, $reverse);
        }

        return $lines;
    }

    private static function line(array $f, $accountType, $account, $debit, $credit, $reverse)
    {
        if ($reverse) {
            list($debit, $credit) = [$credit, $debit];
        }

        return [
            'JournalName'     => LANDLORD_INV_JOURNAL_NAME,
            'JournalNum'      => $f['journal_num'],
            'PaymentDate'     => $f['invoice_date'],
            'vendAccount'     => (string) $account,
            'currency'        => CURRENCY,
            'accountType'     => $accountType,
            'paymentMethod'   => '',
            'Description'     => $f['description'],
            'documentNo'      => $f['invoice_no'],
            'checkBookid'     => '',
            'AmountCredit'    => floatval($credit),
            'AmountDebit'     => floatval($debit),
            'voucher'         => $f['invoice_no'],
            'Invoice'         => $f['invoice_no'],
            'Remarks'         => $f['description'],
            'dimension1'      => 'Building',
            'dimension1value' => !empty($f['building_code']) ? $f['building_code'] : '000',
            'dimension2'      => 'Division',
            'dimension2value' => !empty($f['ax_division']) ? $f['ax_division'] : AX_DIVISION_PLMS,
            'dimension3'      => 'Employee',
            'dimension3value' => '00000',
            'dimension4'      => 'Location',
            'dimension4value' => '00',
            'dimension5'      => 'Projects',
            'dimension5value' => '00',
            'DataAreaId'      => DATA_AREA_ID,
            'company'         => COMPANY,
        ];
    }
}
