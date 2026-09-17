<?php

namespace Modules\BackOffice\Services;

use Carbon\Carbon;

/**
 * Municipal tax charged to a tenant at hand-over (Vacating Unit Inspection,
 * "Other charges" tab). Business rule:
 *   - normal termination : rate% of the total contract value
 *   - early termination  : balance months (termination date -> contract end,
 *                          part month counts as a whole month) x monthly rent x rate%
 * Pure PHP (no DB) so it can be unit tested; the rate comes from the
 * `municipal_tax_percentage` configuration setting. Runs on PHP < 7.4.
 */
class MunicipalTaxCalculator
{
    const SETTING_KEY = 'municipal_tax_percentage';

    /** termination_type_status values that mean an early (premature) termination */
    const EARLY_STATUSES = [1, 2, 3]; // requested, under approval, approved

    /**
     * @param  object     $contract     needs tenant_contract_value, tenant_contract_rent, tenant_contract_valid_to_date
     * @param  object     $termination  needs termination_type_status, termination_date
     * @param  float|null $rate         percentage, e.g. 3
     * @return array  ['amount' => float(3dp), 'early' => bool, 'months' => int, 'note' => string]
     */
    public static function forTermination($contract, $termination, $rate)
    {
        $rate  = (float) $rate;
        $early = in_array((int) ($termination->termination_type_status ?? 0), self::EARLY_STATUSES, true);
        $rateLabel = rtrim(rtrim(number_format($rate, 2, '.', ''), '0'), '.') . '%';

        if ($early) {
            $months = self::balanceMonths($termination->termination_date ?? null, $contract->tenant_contract_valid_to_date ?? null);
            $rent   = (float) ($contract->tenant_contract_rent ?? 0);
            $amount = $months * $rent * $rate / 100;
            $note   = sprintf('Early termination: %d month%s x %s x %s',
                $months, $months === 1 ? '' : 's', number_format($rent, 3), $rateLabel);
        } else {
            $months = 0;
            $value  = (float) ($contract->tenant_contract_value ?? 0);
            $amount = $value * $rate / 100;
            $note   = sprintf('Normal termination: %s x %s', number_format($value, 3), $rateLabel);
        }

        return [
            'amount' => round($amount, 3),
            'early'  => $early,
            'months' => $months,
            'note'   => $note,
        ];
    }

    /**
     * Whole months of contract left after the termination date, any part
     * month counting as a full month. The termination date is the last day
     * of occupancy, so the balance runs from the day after it to the day
     * after the contract end (30 Sep -> 31 Dec = Oct, Nov, Dec = 3 months).
     * 0 when the termination is on/after the end.
     */
    public static function balanceMonths($terminationDate, $contractEnd)
    {
        if (empty($terminationDate) || empty($contractEnd)) {
            return 0;
        }
        $from = Carbon::parse($terminationDate)->startOfDay()->addDay();
        $to   = Carbon::parse($contractEnd)->startOfDay()->addDay();
        if ($to->lte($from)) {
            return 0;
        }
        $whole = $from->diffInMonths($to);
        $rest  = $from->copy()->addMonthsNoOverflow($whole);
        return $whole + ($rest->lt($to) ? 1 : 0);
    }
}
