<?php

namespace Modules\BackOffice\Services;

/**
 * Cheques the bank rejected on a clearing sheet, matched to their PDC rows so
 * they can be marked bounced from the review page. A rent + VAT cheque is two
 * PDC rows (one physical cheque), so a match is a group of rows of one contract.
 * Pure: no DB. PHP < 7.4: no arrow functions.
 */
class BankSheetRejectedCheques
{
    const BOUNCED = 1; // pdc_cancel_reason

    /** Active bounce reasons (Masters -> Bounce Reasons), code => name. */
    public static function reasons()
    {
        return \Modules\Masters\Entities\PdcBounceReason::options();
    }

    /**
     * Bounce reason code for the bank's rejection text, from the "bank sheet words"
     * on Masters -> Bounce Reasons; null when nothing matches (the user picks).
     * $matchers: PdcBounceReason::matchers() shape; loaded from the master when null.
     */
    public static function bounceReasonFor($bankText, array $matchers = null)
    {
        return \Modules\Masters\Entities\PdcBounceReason::match($bankText,
            $matchers !== null ? $matchers : \Modules\Masters\Entities\PdcBounceReason::matchers());
    }

    /**
     * @param array $rows    rejected sheet rows (BankSheetReader shape)
     * @param array $pdcs    PDC rows (objects) with id, pdc_check_no, bank_id, pdc_check_date,
     *                       tenant_contract_id, pdc_cancel_reason (0 open, 1 bounced)
     * @param array $bankMap sheet bank code => pdc.bank_id or list of ids
     * @return array per row: row, status (found|choose|not_found|already_bounced), groups [[pdc,...],...]
     */
    public static function match(array $rows, array $pdcs, array $bankMap)
    {
        $byCheque = [];
        foreach ($pdcs as $p) {
            $byCheque[BankSheetPdcMatcher::normaliseChequeNo($p->pdc_check_no)][] = $p;
        }

        $out = [];
        foreach ($rows as $row) {
            $same = isset($byCheque[BankSheetPdcMatcher::normaliseChequeNo($row['cheque_no'])])
                ? $byCheque[BankSheetPdcMatcher::normaliseChequeNo($row['cheque_no'])] : [];

            // the sheet's bank narrows it when it is mapped to PLMS banks
            $code = (string) $row['bank_code'];
            if (isset($bankMap[$code])) {
                $banks = array_map('intval', (array) $bankMap[$code]);
                $same = array_values(array_filter($same, function ($p) use ($banks) { return in_array((int) $p->bank_id, $banks, true); }));
            }

            $groups = self::groupByContract($same);
            if (count($groups) > 1 && !empty($row['cheque_date'])) {
                $month = substr($row['cheque_date'], 0, 7);
                $inMonth = array_values(array_filter($groups, function ($g) use ($month) {
                    foreach ($g as $p) {
                        if (substr((string) $p->pdc_check_date, 0, 7) === $month) {
                            return true;
                        }
                    }
                    return false;
                }));
                if (count($inMonth) > 0) {
                    $groups = $inMonth;
                }
            }
            // Still several contracts (different customers' cheques can share a number): the sheet amount decides.
            if (count($groups) > 1 && isset($row['amount']) && (float) $row['amount'] > 0) {
                $month = !empty($row['cheque_date']) ? substr($row['cheque_date'], 0, 7) : null;
                $byAmount = array_values(array_filter($groups, function ($g) use ($row, $month) {
                    $sum = 0.0;
                    foreach ($g as $p) {
                        if ($month === null || substr((string) $p->pdc_check_date, 0, 7) === $month) {
                            $sum += (float) $p->pdc_amt;
                        }
                    }
                    return abs($sum - (float) $row['amount']) < 0.005;
                }));
                if (count($byAmount) > 0) {
                    $groups = $byAmount;
                }
            }

            if (count($groups) === 0) {
                $status = 'not_found';
            } elseif (count($groups) > 1) {
                $status = 'choose';
            } else {
                $allBounced = true;
                foreach ($groups[0] as $p) {
                    if ((int) $p->pdc_cancel_reason !== self::BOUNCED) {
                        $allBounced = false;
                    }
                }
                $status = $allBounced ? 'already_bounced' : 'found';
            }
            $out[] = ['row' => $row, 'status' => $status, 'groups' => $groups];
        }
        return $out;
    }

    private static function groupByContract(array $pdcs)
    {
        $groups = [];
        foreach ($pdcs as $p) {
            $groups[(int) $p->tenant_contract_id][] = $p;
        }
        return array_values($groups);
    }
}
