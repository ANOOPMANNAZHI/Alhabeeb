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
 *                 receipts are issued per calendar month).
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
     * @return array months, rent_due, paid, outstanding, overpaid
     */
    public static function compute($from, $to, $rentPerMonth, $paid)
    {
        $due  = self::rentDue($from, $to, $rentPerMonth);
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
            ->first(['tenant_contract_effective_date', 'tenant_contract_start_date', 'tenant_contract_rent']);
        if (!$contract) {
            return array_merge(self::compute(null, null, 0, 0), ['as_of' => null]);
        }
        if (!$asOf) {
            $asOf = DB::table('termination')->where('contract_id', $contractId)->orderBy('id', 'desc')->value('termination_date') ?: date('Y-m-d');
        }
        $from = $contract->tenant_contract_effective_date ?: $contract->tenant_contract_start_date;
        $r = self::compute($from, $asOf, $contract->tenant_contract_rent, self::paidForContract($contractId));
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
