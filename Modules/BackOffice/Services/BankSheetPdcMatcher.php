<?php

namespace Modules\BackOffice\Services;

/**
 * Matches accepted rows of the bank's cheque-clearing sheet against
 * unposted PDC rows so they can be posted in bulk. One physical cheque may
 * be split across several PDC rows (one per contract/unit of the same
 * tenant), and different tenants' cheques can share a serial number, so a
 * match is a *group* of PDC rows with the same cheque no + bank whose
 * cheque date falls in the sheet's cheque month and whose amounts add up
 * to the sheet amount. Pure PHP (no DB) so it can be unit tested; runs on
 * PHP < 7.4 in production: no arrow functions.
 */
class BankSheetPdcMatcher
{
    const MATCHED         = 'matched';
    const AMBIGUOUS       = 'ambiguous';
    const AMOUNT_MISMATCH = 'amount_mismatch';
    const DATE_MISMATCH   = 'date_mismatch';
    const NOT_FOUND       = 'not_found';
    const UNKNOWN_BANK    = 'unknown_bank';
    const REJECTED        = 'rejected';

    /** Amounts closer than this are equal (OMR has 3 decimals). */
    const AMOUNT_EPSILON = 0.0005;

    /** @var array bank-sheet bank code => pdc.bank_id, or a list of them when cheques of that bank are entered under more than one PLMS bank */
    private $bankMap;

    public function __construct(array $bankMap)
    {
        $this->bankMap = $bankMap;
    }

    /**
     * @param array $sheetRows parsed sheet rows (see BankSheetReader):
     *                         row_no, cheque_no, bank_code, bank_name, amount,
     *                         cheque_date (Y-m-d), accepted, reject_reason
     * @param array $pdcs      unposted PDC rows (objects) with id, pdc_check_no,
     *                         bank_id, pdc_amt, pdc_check_date, tenant_id, ...
     * @return array one result per sheet row, in sheet order:
     *               ['row' => sheetRow, 'status' => const, 'pdcs' => rows to post,
     *                'candidates' => [[rows], ...] when ambiguous, 'reason' => string|null]
     */
    public function match(array $sheetRows, array $pdcs)
    {
        $pool = $this->indexByChequeAndBank($pdcs);
        $used = [];
        $out  = [];

        foreach ($sheetRows as $row) {
            $result = $this->matchRow($row, $pool, $used);
            foreach ($result['pdcs'] as $pdc) {
                $used[$pdc->id] = true;
            }
            $out[] = $result;
        }

        return $out;
    }

    private function matchRow(array $row, array $pool, array $used)
    {
        if (empty($row['accepted'])) {
            return $this->result($row, self::REJECTED, [], [], $row['reject_reason']);
        }

        $bankCode = (string) $row['bank_code'];
        if (!isset($this->bankMap[$bankCode])) {
            return $this->result($row, self::UNKNOWN_BANK, [], [], 'Bank "' . $row['bank_name'] . '" is not mapped to a PLMS bank');
        }
        $found = [];
        foreach ((array) $this->bankMap[$bankCode] as $bankId) {
            $key = $this->key($row['cheque_no'], $bankId);
            if (!isset($pool[$key])) {
                continue;
            }
            foreach ($pool[$key] as $pdc) {
                if (!isset($used[$pdc->id])) {
                    $found[] = $pdc;
                }
            }
        }
        if (count($found) === 0) {
            return $this->result($row, self::NOT_FOUND, [], [], 'No unposted PDC with this cheque number and bank');
        }

        $month   = substr($row['cheque_date'], 0, 7);
        $inMonth = [];
        foreach ($found as $pdc) {
            if (substr((string) $pdc->pdc_check_date, 0, 7) === $month) {
                $inMonth[] = $pdc;
            }
        }
        if (count($inMonth) === 0) {
            $dates = array_unique(array_map(function ($p) { return substr((string) $p->pdc_check_date, 0, 10); }, $found));
            return $this->result($row, self::DATE_MISMATCH, [], [], 'PLMS cheque date ' . implode(', ', $dates) . ' is not in ' . $month);
        }

        $candidates = $this->candidateGroups($inMonth, (float) $row['amount']);

        if (count($candidates) === 1) {
            return $this->result($row, self::MATCHED, $candidates[0], [], null);
        }
        if (count($candidates) > 1) {
            return $this->result($row, self::AMBIGUOUS, [], $candidates, count($candidates) . ' possible matches');
        }

        $totals = [];
        foreach ($this->groupByTenant($inMonth) as $group) {
            $totals[] = $this->fmt($this->sum($group));
        }
        return $this->result(
            $row,
            self::AMOUNT_MISMATCH,
            [],
            [],
            'PLMS total ' . implode(' / ', $totals) . ' does not equal bank amount ' . $this->fmt((float) $row['amount'])
        );
    }

    /**
     * Groups of PDC rows whose amounts add up to the sheet amount. A tenant's
     * rows on one cheque are taken together (split cheque); failing that,
     * any single row of the right amount is its own candidate.
     */
    private function candidateGroups(array $pdcs, $amount)
    {
        $candidates = [];
        foreach ($this->groupByTenant($pdcs) as $group) {
            if ($this->sameAmount($this->sum($group), $amount)) {
                $candidates[] = array_values($group);
                continue;
            }
            foreach ($group as $pdc) {
                if ($this->sameAmount((float) $pdc->pdc_amt, $amount)) {
                    $candidates[] = [$pdc];
                }
            }
        }
        return $candidates;
    }

    private function groupByTenant(array $pdcs)
    {
        $groups = [];
        foreach ($pdcs as $pdc) {
            $groups[(string) $pdc->tenant_id][] = $pdc;
        }
        return array_values($groups);
    }

    private function indexByChequeAndBank(array $pdcs)
    {
        $pool = [];
        foreach ($pdcs as $pdc) {
            $pool[$this->key($pdc->pdc_check_no, $pdc->bank_id)][] = $pdc;
        }
        return $pool;
    }

    private function key($chequeNo, $bankId)
    {
        return self::normaliseChequeNo($chequeNo) . '|' . (int) $bankId;
    }

    /** Bank pads cheque numbers with leading zeros; PLMS stores them bare. */
    public static function normaliseChequeNo($chequeNo)
    {
        $bare = ltrim(trim((string) $chequeNo), '0');
        return $bare === '' ? '0' : $bare;
    }

    private function sum(array $pdcs)
    {
        $total = 0.0;
        foreach ($pdcs as $pdc) {
            $total += (float) $pdc->pdc_amt;
        }
        return $total;
    }

    private function sameAmount($a, $b)
    {
        return abs($a - $b) < self::AMOUNT_EPSILON;
    }

    private function fmt($amount)
    {
        return rtrim(rtrim(number_format($amount, 3, '.', ''), '0'), '.');
    }

    private function result(array $row, $status, array $pdcs, array $candidates, $reason)
    {
        return [
            'row'        => $row,
            'status'     => $status,
            'pdcs'       => $pdcs,
            'candidates' => $candidates,
            'reason'     => $reason,
        ];
    }
}
