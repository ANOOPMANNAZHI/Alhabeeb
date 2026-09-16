<?php

namespace Modules\BackOffice\Services;

use InvalidArgumentException;

/**
 * Turns a management-fee phrase from the "NM Buildings Fees & Cleaning
 * Expenses" sheet (e.g. "300/- P. M. & 4% on New Leasing & RO 20/- for
 * Renewal") into landlord_contract columns. Pure PHP, unit tested, PHP 7.1
 * compatible (production runs PHP < 7.4).
 *
 * Output keys (always present):
 *   management_method                          1 = Percentage, 2 = Amount
 *   landlord_contract_management_fee           % or OMR per month
 *   landlord_contract_facility_management_fee  OMR per month or null
 *   landlord_contract_renewal_fee              OMR per renewal or null
 *   landlord_contract_new_leasing_fee_type     1 = Percentage, 2 = Amount, or null
 *   landlord_contract_new_leasing_fee          value or null
 *   landlord_contract_cleaning_charge          OMR per month, or null = leave unchanged
 */
class LandlordContractFeeRuleParser
{
    const METHOD_PERCENTAGE = 1;
    const METHOD_AMOUNT     = 2;

    /**
     * @param  string $feePhrase
     * @param  string $cleaning  raw cell; '' means "do not touch"
     * @return array
     * @throws InvalidArgumentException when a part of the phrase is not understood
     */
    public static function parse($feePhrase, $cleaning)
    {
        $out = [
            'management_method'                         => null,
            'landlord_contract_management_fee'          => null,
            'landlord_contract_facility_management_fee' => null,
            'landlord_contract_renewal_fee'             => null,
            'landlord_contract_new_leasing_fee_type'    => null,
            'landlord_contract_new_leasing_fee'         => null,
            'landlord_contract_cleaning_charge'         => null,
        ];

        // Components are joined by "&" or "+" in the sheet.
        $parts = preg_split('/\s*[&+]\s*/', trim($feePhrase));
        foreach ($parts as $part) {
            $part = trim($part);
            if ($part === '') {
                continue;
            }
            $value = self::number($part);
            $isPct = strpos($part, '%') !== false;

            if (preg_match('/facility/i', $part)) {
                $out['landlord_contract_facility_management_fee'] = $value;
            } elseif (preg_match('/ren[e]?wal/i', $part)) {        // "Renewal" and the sheet's "Renwal" typo
                $out['landlord_contract_renewal_fee'] = $value;
            } elseif (preg_match('/new\s*leas/i', $part)) {
                $out['landlord_contract_new_leasing_fee_type'] = $isPct ? self::METHOD_PERCENTAGE : self::METHOD_AMOUNT;
                $out['landlord_contract_new_leasing_fee']      = $value;
            } elseif (preg_match('/\bP\.?\s*A\.?$/i', $part)) {     // "100/- P.A." → per month
                self::setManagement($out, self::METHOD_AMOUNT, round($value / 12, 3), $part);
            } elseif (preg_match('/\bP\.?\s*M\.?$/i', $part)) {     // "5% P. M." / "400/- P.M." / "50 P.M."
                self::setManagement($out, $isPct ? self::METHOD_PERCENTAGE : self::METHOD_AMOUNT, $value, $part);
            } else {
                throw new InvalidArgumentException('Unsupported fee rule part: "' . $part . '"');
            }
        }

        if ($out['management_method'] === null) {
            throw new InvalidArgumentException('No management fee found in: "' . $feePhrase . '"');
        }

        $cleaning = trim((string) $cleaning);
        if ($cleaning !== '') {
            $out['landlord_contract_cleaning_charge'] = (float) str_replace(',', '', $cleaning);
        }

        return $out;
    }

    private static function setManagement(array &$out, $method, $value, $part)
    {
        if ($out['management_method'] !== null) {
            throw new InvalidArgumentException('Second management fee in the same rule: "' . $part . '"');
        }
        $out['management_method']                = $method;
        $out['landlord_contract_management_fee'] = $value;
    }

    /** First number in the part, commas stripped ("1,500/-" → 1500.0). */
    private static function number($part)
    {
        if (!preg_match('/(\d[\d,]*(?:\.\d+)?)/', $part, $m)) {
            throw new InvalidArgumentException('No amount in fee rule part: "' . $part . '"');
        }
        return (float) str_replace(',', '', $m[1]);
    }
}
