<?php

namespace Modules\BackOffice\Services;

/**
 * Clearing sheet codes the bank's cheque sheet uses but no PLMS bank carries yet
 * (bank.bank_clearing_codes), so they can be added from the sheet preview.
 * The code is the number before " - " in the sheet's Pay Bank column ("45 - Commercial Bank").
 * PHP < 7.4: no arrow functions.
 */
class BankClearingCodes
{
    /**
     * Unknown codes on the sheet, from the matcher's results (status unknown_bank).
     * A row votes for a PLMS bank when the same cheque number and amount sit under
     * exactly one bank in PLMS; the code is suggested for a bank only when one bank
     * leads the votes.
     *
     * @return array code => ['code', 'sheet_name', 'rows', 'suggested_bank_id' (int|null), 'votes' => [bank id => n]]
     */
    public static function unknown(array $results)
    {
        $out = [];
        foreach ($results as $r) {
            if ($r['status'] !== BankSheetPdcMatcher::UNKNOWN_BANK) {
                continue;
            }
            $code = (string) $r['row']['bank_code'];
            if (!self::valid($code)) {
                continue; // no number on the sheet ("Some Bank"): nothing to save
            }
            if (!isset($out[$code])) {
                $out[$code] = ['code' => $code, 'sheet_name' => self::sheetName($r['row']['bank_name']), 'rows' => 0,
                    'suggested_bank_id' => null, 'votes' => []];
            }
            $out[$code]['rows']++;

            $banks = [];
            if (isset($r['related_kind']) && $r['related_kind'] === BankSheetPdcMatcher::RELATED_OTHER_BANK) {
                foreach ($r['related'] as $pdc) {
                    if (abs((float) $pdc->pdc_amt - (float) $r['row']['amount']) < 0.0005) {
                        $banks[(int) $pdc->bank_id] = true;
                    }
                }
                if (!$banks) {
                    // a split cheque: several PDC rows of one bank adding up to the amount
                    $sum = [];
                    foreach ($r['related'] as $pdc) {
                        $b = (int) $pdc->bank_id;
                        $sum[$b] = (isset($sum[$b]) ? $sum[$b] : 0) + (float) $pdc->pdc_amt;
                    }
                    foreach ($sum as $b => $total) {
                        if (abs($total - (float) $r['row']['amount']) < 0.0005) {
                            $banks[$b] = true;
                        }
                    }
                }
            }
            if (count($banks) === 1) {
                $b = key($banks);
                $out[$code]['votes'][$b] = (isset($out[$code]['votes'][$b]) ? $out[$code]['votes'][$b] : 0) + 1;
            }
        }
        foreach ($out as $code => $u) {
            if ($u['votes']) {
                arsort($u['votes']);
                $top = array_values($u['votes']);
                if (count($top) === 1 || $top[0] > $top[1]) {
                    $out[$code]['suggested_bank_id'] = (int) key($u['votes']);
                }
            }
        }
        ksort($out, SORT_NATURAL);
        return $out;
    }

    /** "45 - Commercial Bank of Oman" => "Commercial Bank of Oman" */
    public static function sheetName($bankName)
    {
        return trim(preg_replace('/^\s*\d+\s*-\s*/', '', (string) $bankName));
    }

    public static function valid($code)
    {
        return (bool) preg_match('/^\d{1,10}$/', (string) $code);
    }

    /** The bank's codes as a list ("27, 34,41" => ['27', '34', '41']). */
    public static function split($codes)
    {
        $out = [];
        foreach (explode(',', (string) $codes) as $c) {
            $c = trim($c);
            if ($c !== '' && !in_array($c, $out, true)) {
                $out[] = $c;
            }
        }
        return $out;
    }

    /** Adds $code next to the codes already saved ("18" + "45" => "18,45"); unchanged when present. */
    public static function add($codes, $code)
    {
        $list = self::split($codes);
        if (!in_array((string) $code, $list, true)) {
            $list[] = (string) $code;
        }
        return implode(',', $list);
    }
}
