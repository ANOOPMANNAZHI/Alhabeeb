<?php

namespace Modules\BackOffice\Services;

/**
 * Sheet cheques that were already posted: a later upload of the same (or an
 * overlapping) bank sheet finds no unposted PDC for them, so without this they
 * would sit in "Cannot post" next to the cheques that really need attention.
 * A cheque counts as posted when posted PDCs carry its cheque no + bank, dated
 * in the sheet's cheque month or the BankSheetPdcMatcher::MONTHS_BACK months
 * before it (nearest first), the same window the matcher uses.
 * Pure: no DB. PHP < 7.4: no arrow functions.
 */
class BankSheetPostedCheques
{
    /**
     * @param array $rows    sheet rows (BankSheetReader shape: row_no, cheque_no, bank_code, amount, cheque_date)
     * @param array $pdcs    posted PDC rows (objects) with id, pdc_check_no, bank_id, pdc_amt, pdc_check_date
     * @param array $bankMap sheet bank code => pdc.bank_id or list of ids
     * @return array row_no => ['pdcs' => [posted pdc rows], 'amount' => sum, 'same_amount' => bool];
     *               rows with no posted PDC are left out
     */
    public static function find(array $rows, array $pdcs, array $bankMap)
    {
        $byCheque = [];
        foreach ($pdcs as $p) {
            $byCheque[BankSheetPdcMatcher::normaliseChequeNo($p->pdc_check_no)][] = $p;
        }

        $out = [];
        foreach ($rows as $row) {
            $code = (string) $row['bank_code'];
            $no   = BankSheetPdcMatcher::normaliseChequeNo($row['cheque_no']);
            if (!isset($bankMap[$code]) || !isset($byCheque[$no]) || empty($row['cheque_date'])) {
                continue;
            }
            $banks = array_map('intval', (array) $bankMap[$code]);
            $same = [];
            foreach ($byCheque[$no] as $p) {
                if (in_array((int) $p->bank_id, $banks, true)) {
                    $same[] = $p;
                }
            }

            $month = substr($row['cheque_date'], 0, 7);
            $found = [];
            for ($back = 0; count($found) === 0 && $back <= BankSheetPdcMatcher::MONTHS_BACK; $back++) {
                $m = $back === 0 ? $month : date('Y-m', strtotime($month . '-01 -' . $back . ' month'));
                foreach ($same as $p) {
                    if (substr((string) $p->pdc_check_date, 0, 7) === $m) {
                        $found[] = $p;
                    }
                }
            }
            if (count($found) === 0) {
                continue;
            }

            $sum = 0.0;
            foreach ($found as $p) {
                $sum += (float) $p->pdc_amt;
            }
            $out[$row['row_no']] = [
                'pdcs'        => $found,
                'amount'      => round($sum, 3),
                'same_amount' => abs($sum - (float) $row['amount']) < BankSheetPdcMatcher::AMOUNT_EPSILON,
            ];
        }
        return $out;
    }
}
