<?php

namespace Modules\BackOffice\Services;

/**
 * Matches accepted rows of the bank's cheque-clearing sheet against
 * unposted PDC rows so they can be posted in bulk. One physical cheque may
 * be split across several PDC rows (one per contract/unit of the same
 * tenant), and different tenants' cheques can share a serial number, so a
 * match is a *group* of PDC rows with the same cheque no + bank whose
 * cheque date falls in the sheet's cheque month - or, when none does, in one of
 * the MONTHS_BACK months before it, nearest first (an October cheque saved as
 * September, August or July; the result then carries 'prev_month') - and whose
 * amounts add up to the sheet amount. Pure PHP (no DB) so it can be unit tested; runs on
 * PHP < 7.4 in production: no arrow functions.
 */
class BankSheetPdcMatcher
{
    const MONTHS_BACK     = 3; // a cheque may be saved in PLMS up to 3 months before its date (user, 04/10/2026)
    const MATCHED         = 'matched';
    const AMBIGUOUS       = 'ambiguous';
    const AMOUNT_MISMATCH = 'amount_mismatch';
    const DATE_MISMATCH   = 'date_mismatch';
    const NOT_FOUND       = 'not_found';
    const UNKNOWN_BANK    = 'unknown_bank';
    const REJECTED        = 'rejected';
    const VAT_CHECK       = 'vat_check';
    const ALREADY_POSTED  = 'already_posted'; // set by the controller (BankSheetPostedCheques), never by match()
    const REDEPOSIT_NEEDED = 'redeposit_needed'; // set by the controller: a bounced cheque deposited again, never by match()

    /** Amounts closer than this are equal (OMR has 3 decimals). */
    const AMOUNT_EPSILON = 0.0005;

    const VAT_RATE       = 0.05;
    const VAT_TOLERANCE  = 1.0;
    const VAT_PDC_BAND   = 0.005; // an "Others" PDC is VAT when within this share of the rent of 5% (or VAT_TOLERANCE)
    const COMPREHENSIVE  = 1;
    const PDC_TYPE_OTHERS = 3; // "Others": VAT entered before the VAT type existed, or a charge (E&W, fees ...)
    const PDC_TYPE_VAT    = 4; // "VAT": always VAT, whatever the amount

    /** 'related' on a result that cannot be posted: PDCs shown for reference only, never posted. */
    const RELATED_SAME       = 'same';       // same cheque no + bank, amount or month differs
    const RELATED_OTHER_BANK = 'other_bank'; // same cheque no, but on a PLMS bank the sheet bank does not map to

    /** A VAT or "Others" PDC: posted with (or apart from) the rent, never as rent. */
    public static function isVat($pdc)
    {
        return isset($pdc->pdc_type) && in_array((int) $pdc->pdc_type, [self::PDC_TYPE_OTHERS, self::PDC_TYPE_VAT], true);
    }

    /** A PDC entered with the VAT type. */
    public static function isVatType($pdc)
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
            if ($result['status'] === self::MATCHED || $result['status'] === self::VAT_CHECK) {
                $result['account_mismatch'] = $this->accountDiffers($row, $result['pdcs']);
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
            $vatOnly = [];
            foreach ($vatFound as $pdc) {
                if (self::isVatType($pdc)) {
                    $vatOnly[] = $pdc;
                }
            }
            if (count($vatOnly) > 0) {
                return $this->matchVatOnly($row, $vatOnly);
            }
            return $this->result($row, self::NOT_FOUND, [], [], 'No unposted PDC with this cheque number and bank');
        }

        list($inMonth, $prevMonth) = $this->inWindow($found, $row['cheque_date']);
        if (count($inMonth) === 0) {
            return $this->dateMismatch($row, $found);
        }

        $result = $this->matchAmounts($row, $inMonth, $vatFound);
        if ($prevMonth !== null) {
            $result['prev_month'] = $prevMonth;
        }
        return $result;
    }

    /**
     * [PDCs dated in the cheque's month, null] or, when none is, those of the nearest
     * of the MONTHS_BACK months before it with the month found; [[], null] when none.
     */
    private function inWindow(array $pdcs, $chequeDate)
    {
        $month   = substr($chequeDate, 0, 7);
        $inMonth = $this->inMonth($pdcs, $month);
        $prevMonth = null;
        for ($back = 1; count($inMonth) === 0 && $back <= self::MONTHS_BACK; $back++) {
            $before = date('Y-m', strtotime($month . '-01 -' . $back . ' month'));
            $inMonth = $this->inMonth($pdcs, $before);
            $prevMonth = count($inMonth) > 0 ? $before : null;
        }
        return [$inMonth, $prevMonth];
    }

    private function dateMismatch(array $row, array $found)
    {
        $dates = array_unique(array_map(function ($p) { return substr((string) $p->pdc_check_date, 0, 10); }, $found));
        return $this->withRelated(
            $this->result($row, self::DATE_MISMATCH, [], [], 'PLMS cheque date ' . implode(', ', $dates) . ' is not in ' . substr($row['cheque_date'], 0, 7)
                . ' or the ' . self::MONTHS_BACK . ' months before'),
            $found,
            self::RELATED_SAME
        );
    }

    /**
     * A separate VAT cheque: only VAT-type PDCs carry its number. Same month window and
     * amount rules as rent; the result carries 'vat_only' so posting books VAT receipts.
     */
    private function matchVatOnly(array $row, array $vatPdcs)
    {
        list($inMonth, $prevMonth) = $this->inWindow($vatPdcs, $row['cheque_date']);
        if (count($inMonth) === 0) {
            return $this->dateMismatch($row, $vatPdcs);
        }

        $amount = (float) $row['amount'];
        $candidates = $this->candidateGroups($inMonth, $amount);
        if (count($candidates) === 0 && count($this->groupByTenant($inMonth)) > 1 && $this->sameAmount($this->sum($inMonth), $amount)) {
            $candidates = [$inMonth]; // one VAT cheque for several tenants
        }
        if (count($candidates) > 1) {
            $byAccount = $this->groupsWithAccount($candidates, isset($row['account_no']) ? $row['account_no'] : null);
            if (count($byAccount) === 1) {
                $candidates = $byAccount;
            }
        }

        if (count($candidates) === 1) {
            $out = $this->result($row, self::MATCHED, $candidates[0], [], 'Separate VAT cheque');
        } elseif (count($candidates) > 1) {
            $out = $this->result($row, self::AMBIGUOUS, [], $candidates, count($candidates) . ' possible VAT matches');
        } else {
            $totals = [];
            foreach ($this->groupByTenant($inMonth) as $group) {
                $totals[] = $this->fmt($this->sum($group));
            }
            return $this->withRelated(
                $this->result($row, self::AMOUNT_MISMATCH, [], [], 'PLMS VAT total ' . implode(' / ', $totals) . ' does not equal bank amount ' . $this->fmt($amount)),
                $inMonth,
                self::RELATED_SAME
            );
        }
        $out['vat_only'] = true;
        if ($prevMonth !== null) {
            $out['prev_month'] = $prevMonth;
        }
        return $out;
    }

    /** PDCs whose cheque date is in $month (Y-m). */
    private function inMonth(array $pdcs, $month)
    {
        $out = [];
        foreach ($pdcs as $pdc) {
            if (substr((string) $pdc->pdc_check_date, 0, 7) === $month) {
                $out[] = $pdc;
            }
        }
        return $out;
    }

    /** The amount rules, on the rent PDCs of the accepted month(s). */
    private function matchAmounts(array $row, array $inMonth, array $vatFound)
    {

        $amount     = (float) $row['amount'];
        $candidates = $this->candidateGroups($inMonth, $amount);
        if (count($candidates) === 0) {
            // Case A: an agreement's rent rows + its VAT PDC(s) on this cheque add up exactly
            $candidates = $this->rentPlusVatGroups($inMonth, $vatFound, $amount);
        }
        if (count($candidates) === 0) {
            // rent + the agreement's other "Others" PDCs on this cheque (E&W, renewal fee, ...) add up exactly:
            // those are posted as general receipts, like the PDC Posting screen does ('charge_ids')
            $charged = $this->rentPlusChargesGroups($inMonth, $vatFound, $amount);
            if (count($charged) === 1) {
                $out = $this->result($row, self::MATCHED, $charged[0]['pdcs'], [], null);
                $out['charge_ids'] = $charged[0]['charge_ids'];
                return $out;
            }
        }
        if (count($candidates) === 0) {
            // One cheque paying for several tenants (e.g. group companies): the same split rule as
            // within one tenant, but only when ALL of this cheque's PDCs for the month add up exactly.
            $tenantGroups = $this->groupByTenant($inMonth);
            if (count($tenantGroups) > 1 && !$this->sameAmount($this->sum($inMonth), $amount)
                && $this->sameAmount($this->sum($this->withVat($inMonth, $vatFound)), $amount)) {
                $inMonth = $this->withVat($inMonth, $vatFound);   // their VAT is on the same cheque too
            }
            if (count($tenantGroups) > 1 && $this->sameAmount($this->sum($inMonth), $amount)) {
                $names = [];
                foreach ($inMonth as $pdc) {
                    $name = trim((string) (isset($pdc->tenant_name) ? $pdc->tenant_name : ''));
                    if ($name !== '' && !in_array($name, $names, true)) {
                        $names[] = $name;
                    }
                }
                return $this->result($row, self::MATCHED, $inMonth, [],
                    'One cheque for ' . count($tenantGroups) . ' tenants: ' . implode(', ', $names));
            }
        }

        if (count($candidates) > 1) {
            // the drawer's account on the sheet settles it when exactly one candidate carries it
            $byAccount = $this->groupsWithAccount($candidates, isset($row['account_no']) ? $row['account_no'] : null);
            if (count($byAccount) === 1) {
                return $this->result($row, self::MATCHED, $byAccount[0], [], 'Picked by account number');
            }
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

    /**
     * Rent rows of one agreement + that agreement's VAT PDCs, when their sum is the bank amount;
     * failing that, all of one tenant's agreements on the cheque with each one's VAT (e.g. two
     * flats, 280 + 14 each, paid with one cheque of 588).
     */
    private function rentPlusVatGroups(array $rent, array $vat, $amount)
    {
        $out = [];
        foreach ($this->groupByContract($rent) as $contractId => $rows) {
            $vatRows = $this->vatOfContract($vat, $contractId, $this->sum($rows));
            if (count($vatRows) === 0) {
                continue;
            }
            $group = array_merge($rows, $vatRows);
            if ($this->sameAmount($this->sum($group), $amount)) {
                $out[] = $group;
            }
        }
        if (count($out) === 0) {
            foreach ($this->groupByTenant($rent) as $rows) {
                if (count($this->groupByContract($rows)) < 2) {
                    continue; // one agreement: done above
                }
                $group = $this->withVat($rows, $vat);
                if (count($group) > count($rows) && $this->sameAmount($this->sum($group), $amount)) {
                    $out[] = $group;
                }
            }
        }
        return $out;
    }

    /**
     * Per tenant: rent rows + every "Others" PDC of those agreements on the cheque dated in the same
     * month(s), when the total is the bank amount and at least one of them is not VAT-like.
     * @return array of ['pdcs', 'charge_ids' => ids of the "Others" that are not VAT]
     */
    private function rentPlusChargesGroups(array $rent, array $others, $amount)
    {
        $out = [];
        foreach ($this->groupByTenant($rent) as $rows) {
            $contracts = [];
            $months = [];
            foreach ($rows as $r) {
                $contracts[(int) $r->tenant_contract_id] = true;
                $months[substr((string) $r->pdc_check_date, 0, 7)] = true;
            }
            $mine = [];
            foreach ($others as $o) {
                if (isset($contracts[(int) $o->tenant_contract_id]) && isset($months[substr((string) $o->pdc_check_date, 0, 7)])) {
                    $mine[] = $o;
                }
            }
            if (count($mine) === 0) {
                continue;
            }
            $group = array_merge($rows, $mine);
            if (!$this->sameAmount($this->sum($group), $amount)) {
                continue;
            }
            // per agreement: its "Others" together look like VAT -> all VAT; else the one closest to 5% of
            // its rent, if within the VAT band, is VAT and the rest are charges
            $vatIds = [];
            foreach ($this->groupByContract($rows) as $contractId => $contractRows) {
                $rentSum = $this->sum($contractRows);
                $all = $this->vatOfContract($mine, $contractId, $rentSum);
                if (count($all) > 0) {
                    foreach ($all as $v) {
                        $vatIds[(int) $v->id] = true;
                    }
                    continue;
                }
                $best = null;
                foreach ($mine as $o) {
                    if ((int) $o->tenant_contract_id === (int) $contractId && count($this->vatOfContract([$o], $contractId, $rentSum)) > 0
                        && ($best === null || abs((float) $o->pdc_amt - $rentSum * self::VAT_RATE) < abs((float) $best->pdc_amt - $rentSum * self::VAT_RATE))) {
                        $best = $o;
                    }
                }
                if ($best !== null) {
                    $vatIds[(int) $best->id] = true;
                }
            }
            $chargeIds = [];
            foreach ($mine as $o) {
                if (!isset($vatIds[(int) $o->id])) {
                    $chargeIds[] = (int) $o->id;
                }
            }
            if (count($chargeIds) > 0) {
                $out[] = ['pdcs' => $group, 'charge_ids' => $chargeIds];
            }
        }
        return $out;
    }

    /** Rent rows + the VAT PDCs of each of their agreements (those that look like VAT on its rent). */
    private function withVat(array $rent, array $vat)
    {
        $out = $rent;
        foreach ($this->groupByContract($rent) as $contractId => $rows) {
            foreach ($this->vatOfContract($vat, $contractId, $this->sum($rows)) as $v) {
                $out[] = $v;
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
                    'pdcs'     => array_merge($rows, $this->vatOfContract($vat, $contractId, $rentSum)),
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

    /**
     * The agreement's VAT PDCs on this cheque. VAT-type rows always are; when it has
     * none, its "Others" rows only when together they look like VAT on its rent: within
     * ±1.000 OMR or ±0.5% of the rent (whichever is wider) of 5%. Other charges filed
     * as "Others" are left out.
     */
    private function vatOfContract(array $vat, $contractId, $rentSum)
    {
        $out = [];
        $typed = [];
        foreach ($vat as $pdc) {
            if ((int) $pdc->tenant_contract_id === (int) $contractId) {
                $out[] = $pdc;
                if (self::isVatType($pdc)) {
                    $typed[] = $pdc;
                }
            }
        }
        if (count($typed) > 0) {
            return $typed;
        }
        if (count($out) === 0) {
            return [];
        }
        $slack = max(self::VAT_TOLERANCE, $rentSum * self::VAT_PDC_BAND);
        return abs($this->sum($out) - $rentSum * self::VAT_RATE) <= $slack + self::AMOUNT_EPSILON ? $out : [];
    }

    /** Bank pads the drawer's account with leading zeros; PLMS may store it bare. Null when blank. */
    public static function normaliseAccountNo($accountNo)
    {
        $bare = ltrim(preg_replace('/\D/', '', (string) $accountNo), '0');
        return $bare === '' ? null : $bare;
    }

    /** Candidate groups in which every PDC carries the sheet's account number. */
    private function groupsWithAccount(array $groups, $sheetAccount)
    {
        $account = self::normaliseAccountNo($sheetAccount);
        if ($account === null) {
            return [];
        }
        $out = [];
        foreach ($groups as $group) {
            $all = true;
            foreach ($group as $pdc) {
                if (self::normaliseAccountNo(isset($pdc->pdc_account_no) ? $pdc->pdc_account_no : null) !== $account) {
                    $all = false;
                    break;
                }
            }
            if ($all) {
                $out[] = $group;
            }
        }
        return $out;
    }

    /** True when the sheet names an account and a PDC to be posted carries a different one. */
    private function accountDiffers(array $row, array $pdcs)
    {
        $account = self::normaliseAccountNo(isset($row['account_no']) ? $row['account_no'] : null);
        if ($account === null) {
            return false;
        }
        foreach ($pdcs as $pdc) {
            $own = self::normaliseAccountNo(isset($pdc->pdc_account_no) ? $pdc->pdc_account_no : null);
            if ($own !== null && $own !== $account) {
                return true;
            }
        }
        return false;
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
            'account_mismatch' => false, // posted PDC carries a different account than the sheet's Pay Account
        ];
    }
}
