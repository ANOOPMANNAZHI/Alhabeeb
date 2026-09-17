<?php

namespace Modules\BackOffice\Services;

use Carbon\Carbon;

/**
 * Splits a tenant receivable row (from tenantrentreceivable_v2 /
 * tenantrentreceivablecompo_v2) into aging buckets for the Tenant Aging
 * Report. There is no per-installment due ledger, so the buckets are
 * derived: every rent-month after lastpaid_till (or the contract start)
 * up to the as-on date is treated as unpaid and bucketed by how many days
 * overdue its due date is. Buckets are then reconciled so they always sum
 * to netamtdue. Pure PHP (no DB) so it can be unit tested; runs on
 * PHP < 7.4 in production: no arrow functions.
 */
class TenantAgingCalculator
{
    /** Bucket key => [label, min days, max days (null = open ended)] oldest last. */
    const BUCKETS = [
        'current'  => ['Current',   0,   30],
        'b31_60'   => ['31 - 60',   31,  60],
        'b61_90'   => ['61 - 90',   61,  90],
        'b91_120'  => ['91 - 120',  91,  120],
        'b121_180' => ['121 - 180', 121, 180],
        'b180plus' => ['180 +',     181, null],
    ];

    /**
     * Date windows for each bucket, counted back from the as-on date.
     *
     * @return array key => ['label', 'from' (Y-m-d|null), 'to' (Y-m-d)]
     */
    public static function buckets($asOn)
    {
        $asOn = Carbon::parse($asOn)->startOfDay();
        $out  = [];
        foreach (self::BUCKETS as $key => $def) {
            $out[$key] = [
                'label' => $def[0],
                'from'  => $def[2] === null ? null : $asOn->copy()->subDays($def[2])->toDateString(),
                'to'    => $asOn->copy()->subDays($def[1])->toDateString(),
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

        // Walk the unpaid rent-months, each due on the day the period starts.
        if ($unpaidFrom !== null && $rent > 0) {
            $due = $unpaidFrom->copy();
            $i   = 0;
            while ($due->lte($periodEnd) && $i < 600) {
                $days = $due->diffInDays($asOn);
                $amounts[self::bucketFor($days)] += $rent;
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

    private static function bucketFor($days)
    {
        foreach (self::BUCKETS as $key => $def) {
            if ($def[2] === null || $days <= $def[2]) {
                return $key;
            }
        }
        return 'b180plus';
    }
}
