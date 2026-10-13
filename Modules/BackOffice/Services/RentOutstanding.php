<?php

namespace Modules\BackOffice\Services;

use DateTime;
use Illuminate\Support\Facades\DB;

/**
 * Rent outstanding on a tenant contract as on a date (normally the
 * termination date).
 *
 *   rent due    = whole months from the contract's effective date up to and
 *                 including the as-of date, x monthly rent. A started month
 *                 counts as a whole month (same rule as UnpaidRentPeriod, and
 *                 receipts are issued per calendar month). Exception: an early
 *                 termination (before the contract end) dated from 01/08/2026
 *                 is charged by the day with 30-day months, the same function
 *                 as the receivable reports (RentLedger::earlyTerminationDue).
 *   paid        = rent receipts (type 0) that are approved (3) or posted to
 *                 AX (6), not cancelled (status 2), not soft-deleted.
 *   outstanding = max(rent due - paid, 0); anything above is 'overpaid'.
 *
 * Replaces the legacy formula (config/function.php totalContractRentCountCalculation
 * + inline approval_status = 3 sums) which ignored whole years (%m only),
 * charged part months by days, and ignored posted receipts entirely.
 *
 * monthsBetween/rentDue/compute are pure and unit tested; the *ForContract
 * methods read the database. Runs on PHP < 7.4.
 */
class RentOutstanding
{
    const DECIMALS = 3;

    /** approval statuses that mean money really received */
    const COUNTED_APPROVAL = [3, 6];
    const STATUS_CANCELLED = 2;

    /**
     * Whole months occupied from $from up to and including $to.
     * 01/09 -> 30/09 = 1, 01/09 -> 01/10 = 2, 15/01 -> 14/02 = 1.
     */
    public static function monthsBetween($from, $to)
    {
        $start = self::day($from);
        $end   = self::day($to);
        if (!$start || !$end || $end < $start) {
            return 0;
        }
        // Arithmetic on Y/m/d rather than DateTime::diff — PHP < 8.1 mis-reports
        // the day component in non-UTC zones (Asia/Muscat: 01/09 -> 02/10 = "1m 0d").
        $months = ((int) $end->format('Y') - (int) $start->format('Y')) * 12
                + ((int) $end->format('n') - (int) $start->format('n')) + 1;
        if ((int) $end->format('j') < (int) $start->format('j')) {
            $months--; // the last anniversary month has not started yet
        }
        return max($months, 0);
    }

    public static function rentDue($from, $to, $rentPerMonth)
    {
        $rent = self::money($rentPerMonth);
        if ($rent <= 0) {
            return 0.0;
        }
        return round(self::monthsBetween($from, $to) * $rent, self::DECIMALS);
    }

    /**
     * The contract value, when it can be trusted as the rent for the whole
     * term; null otherwise. A started month counts as a whole month, so a
     * contract shorter than a month, or ending part way through one, would be
     * charged more than it is worth (TAG2601977: 16 days = 133.333, not 250).
     * The value is used only when it trims less than one month's rent off the
     * full-term figure: on some old contracts it holds a single month's rent
     * (TAG1901408: 1400 of 16800) and must not be used as a cap.
     */
    public static function contractValueCap($from, $contractEnd, $rentPerMonth, $contractValue)
    {
        $value = self::money($contractValue);
        $rent  = self::money($rentPerMonth);
        if ($value <= 0 || $rent <= 0 || !self::day($contractEnd)) {
            return null;
        }
        $fullTerm = self::rentDue($from, $contractEnd, $rent);
        if ($value > $fullTerm || $fullTerm - $value >= $rent) {
            return null;
        }
        return $value;
    }

    /** How much the contract-value cap lowers rent due up to $to (0 when it does not apply). */
    public static function capReduction($from, $to, $rentPerMonth, $contractValue, $contractEnd)
    {
        $due = self::rentDue($from, $to, $rentPerMonth);
        $cap = self::contractValueCap($from, $contractEnd, $rentPerMonth, $contractValue);
        return ($cap !== null && $due > $cap) ? round($due - $cap, self::DECIMALS) : 0.0;
    }

    /**
     * Correct an outstanding figure stored under the old rule. Removes only the
     * part above the new rule (stored + paid - new due), and never more than the
     * rule's reduction, so re-running it or running it on an already-correct
     * figure changes nothing. Null stays null. $floorAtZero: amounts owed
     * (dues lines) never go below 0; tenant_contract_os is stored unfloored
     * (negative = overpaid), so it may.
     */
    public static function correctedStoredAmount($stored, $paid, $newDue, $reduction, $floorAtZero = true)
    {
        if ($stored === null || $stored === '') {
            return null;
        }
        $stored = self::money($stored);
        $excess = round($stored + self::money($paid) - self::money($newDue), self::DECIMALS);
        $cut    = min(self::money($reduction), max($excess, 0.0));
        $result = round($stored - $cut, self::DECIMALS);
        return $floorAtZero ? max($result, 0.0) : $result;
    }

    /**
     * @param mixed $contractValue / $contractEnd  optional: cap rent due at a
     *        trusted contract value (see contractValueCap)
     * @return array months, rent_due, paid, outstanding, overpaid
     */
    public static function compute($from, $to, $rentPerMonth, $paid, $contractValue = null, $contractEnd = null, $terminated = false)
    {
        $due  = self::isEarlyTermination($from, $to, $contractEnd, $terminated)
            ? RentLedger::earlyTerminationDue($from, $to, $rentPerMonth)
            : self::rentDue($from, $to, $rentPerMonth);
        $cap  = self::contractValueCap($from, $contractEnd, $rentPerMonth, $contractValue);
        if ($cap !== null && $due > $cap) {
            $due = $cap;
        }
        $paid = self::money($paid);
        $net  = round($due - $paid, self::DECIMALS);
        return [
            'months'      => self::monthsBetween($from, $to),
            'rent_due'    => $due,
            'paid'        => $paid,
            'outstanding' => $net > 0 ? $net : 0.0,
            'overpaid'    => $net < 0 ? round(-$net, self::DECIMALS) : 0.0,
        ];
    }

    /**
     * $to is a termination date before the contract end, on or after
     * RentLedger::DAY_COUNT_FROM: rent is then counted by the day with 30-day
     * months, the same as the receivable reports (user, 03/10/2026). Earlier
     * terminations, terminations on the contract end and non-termination dates keep
     * the whole-month rule so settlements already made stay as they are.
     */
    private static function isEarlyTermination($from, $to, $contractEnd, $terminated)
    {
        $start = self::day($from);
        $term  = self::day($to);
        $end   = self::day($contractEnd);
        return $terminated && $start && $term && $end
            && $term->format('Y-m-d') >= RentLedger::DAY_COUNT_FROM
            && $term < $end;
    }

    /** Sum of counted rent receipts on the contract. */
    public static function paidForContract($contractId)
    {
        return (float) DB::table('receipts_generation')
            ->where('tenant_contract_id', $contractId)
            ->where('receipts_generation_type', 0)
            ->whereIn('receipts_generation_approval_status', self::COUNTED_APPROVAL)
            ->where('receipts_generation_status', '<>', self::STATUS_CANCELLED)
            ->whereNull('deleted_at')
            ->sum('receipts_generation_amt');
    }

    /**
     * Outstanding as on $asOf (default: the contract's latest termination
     * date, else today), from the live contract row and receipts.
     *
     * @return array compute() result plus 'as_of' (Y-m-d)
     */
    public static function forContract($contractId, $asOf = null)
    {
        $contract = DB::table('tenant_contracts')->where('id', $contractId)
            ->first(['tenant_contract_effective_date', 'tenant_contract_start_date', 'tenant_contract_rent',
                     'tenant_contract_value', 'tenant_contract_valid_to_date']);
        if (!$contract) {
            return array_merge(self::compute(null, null, 0, 0), ['as_of' => null]);
        }
        // an explicit as-of date is a termination date (Termination Dues passes it); otherwise
        // the latest termination date, else today (not a termination)
        $terminated = (bool) $asOf;
        if (!$asOf) {
            $asOf = DB::table('termination')->where('contract_id', $contractId)->orderBy('id', 'desc')->value('termination_date');
            $terminated = (bool) $asOf;
            $asOf = $asOf ?: date('Y-m-d');
        }
        $from = $contract->tenant_contract_effective_date ?: $contract->tenant_contract_start_date;
        // a legal settlement waiver is a credit on the contract (legal plan phase 4): it settles rent like a
        // receipt but is reported apart, so 'paid' stays the money received
        $paid = self::paidForContract($contractId);
        $waived = \Modules\Masters\Services\LegalWaiver::forContract($contractId);
        $r = self::compute($from, $asOf, $contract->tenant_contract_rent, $paid + $waived,
            $contract->tenant_contract_value, $contract->tenant_contract_valid_to_date, $terminated);
        $r['paid'] = $paid;
        $r['waived'] = $waived;
        $r['as_of'] = $asOf ? date('Y-m-d', strtotime($asOf)) : null;
        return $r;
    }

    /** Outstanding as a plain number, for the legacy call sites. */
    public static function amountForContract($contractId, $asOf = null)
    {
        $r = self::forContract($contractId, $asOf);
        return $r['outstanding'];
    }

    private static function day($value)
    {
        if ($value instanceof DateTime) {
            return (clone $value)->setTime(0, 0, 0);
        }
        if (is_object($value) && method_exists($value, 'format')) {
            $value = $value->format('Y-m-d');
        }
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }
        $ts = strtotime($value);
        // Build the date in the app timezone; a '@timestamp' round trip is off by the UTC offset in Asia/Muscat.
        return $ts === false ? null : new DateTime(date('Y-m-d', $ts));
    }

    private static function money($value)
    {
        if (is_string($value)) {
            $value = str_replace(',', '', $value);
        }
        return round((float) $value, self::DECIMALS);
    }
}
