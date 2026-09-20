<?php

namespace Modules\BackOffice\Services;

use Carbon\Carbon;

/**
 * Splits a tenant receivable row (from tenantrentreceivable_v2 /
 * tenantrentreceivablecompo_v2) into aging buckets for the Tenant Aging
 * Report. There is no per-installment due ledger, so the buckets are
 * derived: every rent-month after lastpaid_till (or the contract start)
 * up to the as-on date is treated as unpaid and bucketed by the calendar
 * month its due date falls in: "Current" is the as-on month (1st .. as-on
 * date), "31 - 60" the previous whole month, and so on. Buckets are then
 * reconciled so they always sum to netamtdue. Pure PHP (no DB) so it can be unit tested; runs on
 * PHP < 7.4 in production: no arrow functions.
 */
class TenantAgingCalculator
{
    /**
     * Bucket key => [label, min months back, max months back (null = open
     * ended)] oldest last, where 0 = the as-on month, 1 = the month before it.
     */
    const BUCKETS = [
        'current'  => ['Current',   0, 0],
        'b31_60'   => ['31 - 60',   1, 1],
        'b61_90'   => ['61 - 90',   2, 2],
        'b91_120'  => ['91 - 120',  3, 3],
        'b121_180' => ['121 - 180', 4, 5],
        'b180plus' => ['180 +',     6, null],
    ];

    /**
     * Date windows for each bucket in whole calendar months back from the
     * as-on date: Current runs from the 1st of the as-on month to the as-on
     * date, every older bucket covers whole months, so the windows are
     * contiguous with no gaps.
     *
     * @return array key => ['label', 'from' (Y-m-d|null), 'to' (Y-m-d)]
     */
    public static function buckets($asOn)
    {
        $asOn  = Carbon::parse($asOn)->startOfDay();
        $month = $asOn->copy()->startOfMonth();
        $out   = [];
        foreach (self::BUCKETS as $key => $def) {
            $out[$key] = [
                'label' => $def[0],
                'from'  => $def[2] === null ? null : $month->copy()->subMonthsNoOverflow($def[2])->toDateString(),
                'to'    => $def[1] === 0
                    ? $asOn->toDateString()
                    : $month->copy()->subMonthsNoOverflow($def[1])->endOfMonth()->toDateString(),
            ];
        }
        return $out;
    }

    /**
     * @param  object $row   needs start_date, lastpaid_till, termination_date, rentper_month, netamtdue
     * @param  string $asOn  Y-m-d
     * @return array  bucket key => amount (3 dp), in BUCKETS order
     */
    public static function age($row, $asOn)
    {
        $keys    = array_keys(self::BUCKETS);
        $amounts = array_fill_keys($keys, 0.0);

        $net = round((float) ($row->netamtdue ?? 0), 3);
        if ($net <= 0) {
            return $amounts;
        }

        $asOn = Carbon::parse($asOn)->startOfDay();
        $rent = (float) ($row->rentper_month ?? 0);

        $unpaidFrom = !empty($row->lastpaid_till)
            ? Carbon::parse($row->lastpaid_till)->startOfDay()->addDay()
            : (!empty($row->start_date) ? Carbon::parse($row->start_date)->startOfDay() : null);

        $periodEnd = $asOn->copy();
        if (!empty($row->termination_date)) {
            $term = Carbon::parse($row->termination_date)->startOfDay();
            if ($term->lt($periodEnd)) {
                $periodEnd = $term;
            }
        }

        // Walk the unpaid rent-months, each due on the day the period starts,
        // and bucket each one by how many calendar months before the as-on
        // month it fell due.
        if ($unpaidFrom !== null && $rent > 0) {
            $due = $unpaidFrom->copy();
            $i   = 0;
            while ($due->lte($periodEnd) && $i < 600) {
                $monthsBack = ($asOn->year * 12 + $asOn->month) - ($due->year * 12 + $due->month);
                $amounts[self::bucketFor($monthsBack)] += $rent;
                $i++;
                $due = $unpaidFrom->copy()->addMonthsNoOverflow($i);
            }
        }

        // Reconcile against the actual balance: partial payments clear the
        // oldest months first, so any shortfall is taken from the oldest
        // buckets; any excess is added to the oldest non-empty bucket.
        $sum  = array_sum($amounts);
        $diff = round($net - $sum, 3);
        if ($sum <= 0) {
            $amounts['current'] = $net;
        } elseif ($diff > 0) {
            foreach (array_reverse($keys) as $key) {
                if ($amounts[$key] > 0) {
                    $amounts[$key] += $diff;
                    break;
                }
            }
        } elseif ($diff < 0) {
            $excess = -$diff;
            foreach (array_reverse($keys) as $key) {
                if ($excess <= 0) {
                    break;
                }
                $take = min($amounts[$key], $excess);
                $amounts[$key] -= $take;
                $excess        -= $take;
            }
        }

        foreach ($amounts as $key => $v) {
            $amounts[$key] = round($v, 3);
        }
        return $amounts;
    }

    private static function bucketFor($monthsBack)
    {
        foreach (self::BUCKETS as $key => $def) {
            if ($def[2] === null || $monthsBack <= $def[2]) {
                return $key;
            }
        }
        return 'b180plus';
    }
}
