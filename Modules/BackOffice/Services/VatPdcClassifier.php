<?php

namespace Modules\BackOffice\Services;

/**
 * Which "Others" (pdc_type 3) PDCs are really VAT, for the one-off move to the
 * VAT type (pdc:convert-vat). The same 5% rule the bank sheet uses
 * (BankSheetPdcMatcher::VAT_RATE within max(VAT_TOLERANCE, VAT_PDC_BAND of rent)):
 *  - on a rent cheque (same cheque no + bank as rent PDCs of the agreement): the
 *    "Others" on it together near 5% of that rent -> all VAT; else the single one
 *    nearest to 5%, if within the band; the rest stay "Others" (charges);
 *  - on its own cheque: near 5% of the agreement's rent PDCs dated in the same
 *    month, else of its nearest rent PDC.
 * Pure: no DB. PHP < 7.4: no arrow functions.
 */
class VatPdcClassifier
{
    const SAME_CHEQUE = 'same cheque as rent';
    const OWN_CHEQUE  = 'own cheque';

    /**
     * @param array $others "Others" PDC rows (objects: id, tenant_contract_id, pdc_check_no, bank_id, pdc_amt, pdc_check_date)
     * @param array $rents  rent PDC rows of the same agreements (same fields)
     * @return array id => ['kind' => SAME_CHEQUE|OWN_CHEQUE, 'rent' => rent compared, 'expected' => 5% of it]
     */
    public static function classify(array $others, array $rents)
    {
        $rentByContract = [];
        foreach ($rents as $r) {
            $rentByContract[(int) $r->tenant_contract_id][] = $r;
        }

        // "Others" on a rent cheque, grouped per agreement + cheque
        $onCheque = [];
        $own = [];
        foreach ($others as $o) {
            $cid = (int) $o->tenant_contract_id;
            $key = self::chequeKey($o);
            $rentRows = [];
            foreach (isset($rentByContract[$cid]) ? $rentByContract[$cid] : [] as $r) {
                if (self::chequeKey($r) === $key) {
                    $rentRows[] = $r;
                }
            }
            if (count($rentRows) > 0) {
                $onCheque[$cid . '|' . $key]['rent'] = $rentRows;
                $onCheque[$cid . '|' . $key]['others'][] = $o;
            } else {
                $own[] = $o;
            }
        }

        $out = [];
        foreach ($onCheque as $g) {
            $rent = self::sum($g['rent']);
            if (self::nearVat(self::sum($g['others']), $rent)) {
                foreach ($g['others'] as $o) {
                    $out[(int) $o->id] = self::entry(self::SAME_CHEQUE, $rent);
                }
                continue;
            }
            $best = null;
            foreach ($g['others'] as $o) {
                if (self::nearVat((float) $o->pdc_amt, $rent)
                    && ($best === null || abs((float) $o->pdc_amt - $rent * BankSheetPdcMatcher::VAT_RATE) < abs((float) $best->pdc_amt - $rent * BankSheetPdcMatcher::VAT_RATE))) {
                    $best = $o;
                }
            }
            if ($best !== null) {
                $out[(int) $best->id] = self::entry(self::SAME_CHEQUE, $rent);
            }
        }

        foreach ($own as $o) {
            $cid = (int) $o->tenant_contract_id;
            $rentRows = isset($rentByContract[$cid]) ? $rentByContract[$cid] : [];
            if (count($rentRows) === 0) {
                continue;
            }
            $month = substr((string) $o->pdc_check_date, 0, 7);
            $sameMonth = [];
            foreach ($rentRows as $r) {
                if (substr((string) $r->pdc_check_date, 0, 7) === $month) {
                    $sameMonth[] = $r;
                }
            }
            if (count($sameMonth) > 0) {
                $rent = self::sum($sameMonth);
            } else {
                $nearest = null;
                foreach ($rentRows as $r) {
                    if ($nearest === null || self::daysApart($r, $o) < self::daysApart($nearest, $o)) {
                        $nearest = $r;
                    }
                }
                $rent = (float) $nearest->pdc_amt;
            }
            if (self::nearVat((float) $o->pdc_amt, $rent)) {
                $out[(int) $o->id] = self::entry(self::OWN_CHEQUE, $rent);
            }
        }
        return $out;
    }

    public static function nearVat($amount, $rent)
    {
        if ($rent <= 0) {
            return false;
        }
        $slack = max(BankSheetPdcMatcher::VAT_TOLERANCE, $rent * BankSheetPdcMatcher::VAT_PDC_BAND);
        return abs($amount - $rent * BankSheetPdcMatcher::VAT_RATE) <= $slack + BankSheetPdcMatcher::AMOUNT_EPSILON;
    }

    private static function entry($kind, $rent)
    {
        return ['kind' => $kind, 'rent' => round($rent, 3), 'expected' => round($rent * BankSheetPdcMatcher::VAT_RATE, 3)];
    }

    private static function chequeKey($pdc)
    {
        return BankSheetPdcMatcher::normaliseChequeNo($pdc->pdc_check_no) . '|' . (int) $pdc->bank_id;
    }

    private static function sum(array $pdcs)
    {
        $t = 0.0;
        foreach ($pdcs as $p) {
            $t += (float) $p->pdc_amt;
        }
        return $t;
    }

    private static function daysApart($a, $b)
    {
        return abs(strtotime(substr((string) $a->pdc_check_date, 0, 10)) - strtotime(substr((string) $b->pdc_check_date, 0, 10)));
    }
}
