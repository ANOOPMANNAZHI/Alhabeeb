<?php

namespace Modules\BackOffice\Services;

/**
 * Turns the raw tax_lines[] / od_lines[] arrays posted by the Landlord
 * Invoice v2 create screen into rows ready for LandlordInvoiceV2Line plus
 * the header totals. Pure PHP (no DB) so the skip / VAT / rounding rules
 * can be unit tested. Runs on PHP < 7.4 in production: no arrow functions.
 */
class LandlordInvoiceV2LineNormalizer
{
    const VAT_RATE = 0.05;

    /**
     * @param  array  $rawLines    [['description', 'amount', 'acc_codes_id'?, ...], ...]
     * @param  string $invoiceType 'tax_invoice' | 'other_deductions'
     * @return array  ['lines' => [...], 'subtotal', 'vat_total', 'grand_total', 'skip' => bool]
     *                skip is true when there are no rows or every amount rounds to 0.000,
     *                meaning no invoice of this type should be created.
     */
    public static function normalize(array $rawLines, $invoiceType)
    {
        $isTax    = $invoiceType === 'tax_invoice';
        $lines    = [];
        $subtotal = 0.0;
        $vatTotal = 0.0;
        $hasValue = false;

        foreach (array_values($rawLines) as $i => $line) {
            $amount = round((float) (isset($line['amount']) ? $line['amount'] : 0), 3);
            $vat    = $isTax ? round($amount * self::VAT_RATE, 3) : 0.0;
            $head   = isset($line['acc_codes_id']) && $line['acc_codes_id'] !== '' && $line['acc_codes_id'] !== null
                ? (int) $line['acc_codes_id']
                : null;

            if ($amount != 0.0) {
                $hasValue = true;
            }
            $subtotal += $amount;
            $vatTotal += $vat;

            $lines[] = [
                'description'  => trim(isset($line['description']) ? $line['description'] : ''),
                'acc_codes_id' => $head,
                'amount'       => $amount,
                'vat_amount'   => $vat,
                'line_order'   => $i + 1,
            ];
        }

        $subtotal = round($subtotal, 3);
        $vatTotal = round($vatTotal, 3);

        return [
            'lines'       => $lines,
            'subtotal'    => $subtotal,
            'vat_total'   => $vatTotal,
            'grand_total' => round($subtotal + $vatTotal, 3),
            'skip'        => !$hasValue,
        ];
    }
}
