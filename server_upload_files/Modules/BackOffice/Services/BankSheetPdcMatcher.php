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
    const VAT_CHECK       = 'vat_check';

    /** Amounts closer than this are equal (OMR has 3 decimals). */
    const AMOUNT_EPSILON = 0.0005;

    const VAT_RATE       = 0.05;
    const VAT_TOLERANCE  = 1.0;
    const COMPREHENSIVE  = 1;
    const PDC_TYPE_VAT   = 3;

    /** 'related' on a result that cannot be posted: PDCs shown for reference only, never posted. */
    const RELATED_SAME       = 'same';       // same cheque no + bank, amount or month differs
    const RELATED_OTHER_BANK = 'other_bank'; // same cheque no, but on a PLMS bank the sheet bank does not map to

    public static function isVat($pdc)
    {
        return isset($pdc->pdc_type) && (int) $pdc->pdc_type === self::PDC_TYPE_VAT;
    }

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
        $pool     = $this->indexByChequeAndBank($pdcs);
        $byCheque = $this->indexByCheque($pdcs);
        $used     = [];
        $out      = [];

        foreach ($sheetRows as $row) {
            $result = $this->matchRow($row, $pool, $used);
            if ($result['status'] === self::NOT_FOUND || $result['status'] === self::UNKNOWN_BANK) {
                $mapped = isset($this->bankMap[(string) $row['bank_code']]) ? (array) $this->bankMap[(string) $row['bank_code']] : [];
                $others = $this->sameChequeOnOtherBanks($row, $byCheque, $used, $mapped);
                if (count($others) > 0) {
                    $result = $this->withRelated($result, $others, self::RELATED_OTHER_BANK);
                }
            }
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

        $vatFound = [];
        $rentFound = [];
        foreach ($found as $pdc) {
            if (self::isVat($pdc)) {
                $vatFound[] = $pdc;
            } else {
                $rentFound[] = $pdc;
            }
        }
        $found = $rentFound;
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
            return $this->withRelated(
                $this->result($row, self::DATE_MISMATCH, [], [], 'PLMS cheque date ' . implode(', ', $dates) . ' is not in ' . $month),
                $found,
                self::RELATED_SAME
            );
        }

        $amount     = (float) $row['amount'];
        $candidates = $this->candidateGroups($inMonth, $amount);
        if (count($candidates) === 0) {
            // Case A: an agreement's rent rows + its VAT PDC(s) on this cheque add up exactly
            $candidates = $this->rentPlusVatGroups($inMonth, $vatFound, $amount);
        }

        if (count($candidates) === 1) {
            return $this->result($row, self::MATCHED, $candidates[0], [], null);
        }
        if (count($candidates) > 1) {
            return $this->result($row, self::AMBIGUOUS, [], $candidates, count($candidates) . ' possible matches');
        }

        // Case B: comprehensive agreement, extra ≈ 5% of its rent
        $vatChecks = $this->vatByDifference($inMonth, $vatFound, $amount);
        if (count($vatChecks) === 1) {
            $c = $vatChecks[0];
            $out = $this->result($row, self::VAT_CHECK, $c['pdcs'], [], 'Rent ' . $this->fmt($c['rent']) . ' + VAT ' . $this->fmt($c['vat']) . ' (5% = ' . $this->fmt($c['expected']) . ')');
            $out['vat_amount']   = $c['vat'];
            $out['expected_vat'] = $c['expected'];
            return $out;
        }

        $totals = [];
        foreach ($this->groupByTenant($inMonth) as $group) {
            $totals[] = $this->fmt($this->sum($group));
        }
        return $this->withRelated(
            $this->result(
                $row,
                self::AMOUNT_MISMATCH,
                [],
                [],
                'PLMS total ' . implode(' / ', $totals) . ' does not equal bank amount ' . $this->fmt($amount)
            ),
            $inMonth,
            self::RELATED_SAME
        );
    }

    /** Unused rent/deposit PDCs with this cheque number on PLMS banks other than $excludeBankIds. */
    private function sameChequeOnOtherBanks(array $row, array $byCheque, array $used, array $excludeBankIds)
    {
        $no = self::normaliseChequeNo($row['cheque_no']);
        if (!isset($byCheque[$no])) {
            return [];
        }
        $exclude = array_flip(array_map('intval', $excludeBankIds));
        $out = [];
        foreach ($byCheque[$no] as $pdc) {
            if (!isset($used[$pdc->id]) && !self::isVat($pdc) && !isset($exclude[(int) $pdc->bank_id])) {
                $out[] = $pdc;
            }
        }
        return $out;
    }

    private function indexByCheque(array $pdcs)
    {
        $index = [];
        foreach ($pdcs as $pdc) {
            $index[self::normaliseChequeNo($pdc->pdc_check_no)][] = $pdc;
        }
        return $index;
    }

    private function withRelated(array $result, array $pdcs, $kind)
    {
        $result['related']      = array_values($pdcs);
        $result['related_kind'] = $kind;
        return $result;
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

    /** Rent rows of one agreement + that agreement's VAT PDCs, when their sum is the bank amount. */
    private function rentPlusVatGroups(array $rent, array $vat, $amount)
    {
        $out = [];
        foreach ($this->groupByContract($rent) as $contractId => $rows) {
            $vatRows = $this->vatOfContract($vat, $contractId);
            if (count($vatRows) === 0) {
                continue;
            }
            $group = array_merge($rows, $vatRows);
            if ($this->sameAmount($this->sum($group), $amount)) {
                $out[] = $group;
            }
        }
        return $out;
    }

    /** @return array of ['pdcs', 'rent', 'vat', 'expected'] for comprehensive agreements whose extra ≈ 5%. */
    private function vatByDifference(array $rent, array $vat, $amount)
    {
        $out = [];
        foreach ($this->groupByContract($rent) as $contractId => $rows) {
            if (!isset($rows[0]->management_id) || (int) $rows[0]->management_id !== self::COMPREHENSIVE) {
                continue;
            }
            $rentSum  = $this->sum($rows);
            $extra    = round($amount - $rentSum, 3);
            $expected = round($rentSum * self::VAT_RATE, 3);
            if ($extra > self::AMOUNT_EPSILON && abs($extra - $expected) <= self::VAT_TOLERANCE + self::AMOUNT_EPSILON) {
                $out[] = [
                    'pdcs'     => array_merge($rows, $this->vatOfContract($vat, $contractId)),
                    'rent'     => $rentSum,
                    'vat'      => $extra,
                    'expected' => $expected,
                ];
            }
        }
        // one cheque, one agreement: rows spread over several agreements are not guessed
        return count($this->groupByContract($rent)) === 1 ? $out : [];
    }

    private function groupByContract(array $pdcs)
    {
        $groups = [];
        foreach ($pdcs as $pdc) {
            $groups[(int) $pdc->tenant_contract_id][] = $pdc;
        }
        return $groups;
    }

    private function vatOfContract(array $vat, $contractId)
    {
        $out = [];
        foreach ($vat as $pdc) {
            if ((int) $pdc->tenant_contract_id === (int) $contractId) {
                $out[] = $pdc;
            }
        }
        return $out;
    }

    private function result(array $row, $status, array $pdcs, array $candidates, $reason)
    {
        return [
            'row'        => $row,
            'status'     => $status,
            'pdcs'       => $pdcs,
            'candidates' => $candidates,
            'reason'     => $reason,
            'vat_amount'   => null,
            'expected_vat' => null,
            'related'      => [],
            'related_kind' => null,
        ];
    }
}
