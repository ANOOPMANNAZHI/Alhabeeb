<?php

namespace Modules\BackOffice\Services;

use Carbon\Carbon;

/**
 * Pure (no DB). What one tenant contract owes on a date, as monthly rent lines.
 *
 * Lines: one per rent period, due on the day the period starts (the effective
 * date, then the same day each month), for periods starting on or before the
 * as-on date and on or before the contract end, or the termination date when
 * terminated (505) on or before the as-on date.
 * Total due:
 *  - terminated by the as-on date: the contract value when terminated on the end
 *    date, else days x rent / 30, half to even. For terminations dated from
 *    DAY_COUNT_FROM on, every month counts 30 days (see thirtyDayCount: 01/08..31/08
 *    and 01/02..28/02 are both one month); earlier ones keep the old v2 count
 *    (termination - start - 1) so settlements made with it stay settled - except a
 *    termination on the last day of a rent month never owes more than the months
 *    used (user, 05/10/2026: the old count added up to 3 days over runs of 31-day
 *    months; where it comes out lower, e.g. over February, it is kept as settled).
 *    A termination on or before the start owes nothing;
 *  - ended by the as-on date, not terminated: the contract value (rent stops at
 *    the end; a tenant who stays on is taken over manually);
 *  - still running: lines x rent, never above the contract value.
 * Lines are trimmed from the latest, or the last one topped up, to add up to the
 * total. Receipts clear the oldest lines first. See docs/superpowers/specs/
 * 2026-10-02-tenant-dues-ledger-design.md. PHP < 7.4 in production.
 */
class RentLedger
{
    const MAX_LINES = 600;
    /** Terminations dated on or after this day count every month as 30 days (user, 02/10/2026). */
    const DAY_COUNT_FROM = '2026-08-01';

    public static function build($c, $asOn, $received)
    {
        $asOn = substr((string) $asOn, 0, 10);
        $eff = substr((string) $c->eff, 0, 10);
        $received = max(0.0, round((float) $received, 3));
        if ($eff === '' || $eff > $asOn) {
            return self::allocate([], 0.0, $received);
        }
        $rent = (float) str_replace(',', '', (string) $c->rent);
        $value = (float) str_replace(',', '', (string) $c->value);
        $vto = !empty($c->vto) ? substr((string) $c->vto, 0, 10) : null;
        $term = !empty($c->term_date) ? substr((string) $c->term_date, 0, 10) : null;
        if ($term !== null && $term > $asOn) {
            $term = null; // terminated after the as-on date: not yet
        }
        $end = $term !== null ? $term : $vto;

        $lines = [];
        $first = Carbon::parse($eff)->startOfDay();
        for ($i = 0; $i < self::MAX_LINES; $i++) {
            $d = $first->copy()->addMonthsNoOverflow($i)->format('Y-m-d');
            if ($d > $asOn || ($end !== null && $d > $end)) {
                break;
            }
            $lines[] = ['due_date' => $d, 'amount' => $rent];
        }
        $sum = $rent * count($lines);

        if ($term !== null) {
            if ($term === $vto) {
                $total = $value > 0 ? $value : $sum;
            } else {
                $total = self::earlyTerminationDue(!empty($c->start) ? $c->start : $eff, $term, $rent);
                if ($value > 0 && $total > $value) {
                    $total = $value; // leaving early never costs more than the whole contract
                }
            }
        } elseif ($vto !== null && $vto <= $asOn) {
            $total = $value > 0 ? $value : $sum;
        } else {
            $total = $value > 0 ? min($sum, $value) : $sum;
        }
        $total = round($total, 3);

        return self::allocate(self::fit($lines, $total, $eff), $total, $received);
    }

    /**
     * Rent owed for a contract terminated early (before its end date) on $term:
     * days x rent / 30, rounded half to even. From DAY_COUNT_FROM every month counts
     * 30 days (thirtyDayCount); earlier terminations keep the old v2 count
     * (termination - start - 1), capped at whole months x rent when they end on the
     * last day of a rent month. Shared by the reports and RentOutstanding (the
     * termination screens and Termination Dues) so both give the same amount.
     */
    public static function earlyTerminationDue($start, $term, $rent)
    {
        $rent = (float) str_replace(',', '', (string) $rent);
        $start = Carbon::parse(substr((string) $start, 0, 10))->startOfDay();
        $termDay = Carbon::parse(substr((string) $term, 0, 10))->startOfDay();
        $days = $termDay->format('Y-m-d') >= self::DAY_COUNT_FROM
            ? self::thirtyDayCount($start, $termDay)
            : $start->diffInDays($termDay, false) - 1;
        $due = $days > 0 ? (float) round($days * ($rent / 30), 0, PHP_ROUND_HALF_EVEN) : 0.0;
        if ($termDay->format('Y-m-d') < self::DAY_COUNT_FROM) {
            $months = self::wholeMonthsEndingOn($start, $termDay);
            if ($months !== null) {
                $due = min($due, (float) round($months * $rent, 3));
            }
        }
        return $due;
    }

    /**
     * Number of whole rent months when $term is the last day of one (the day before
     * the next period starts, same day-of-month rule as the ledger lines); else null.
     */
    private static function wholeMonthsEndingOn(Carbon $start, Carbon $term)
    {
        $dayAfter = $term->copy()->addDay();
        for ($k = 1; $k <= self::MAX_LINES; $k++) {
            $next = $start->copy()->addMonthsNoOverflow($k);
            if ($next->eq($dayAfter)) {
                return $k;
            }
            if ($next->gt($dayAfter)) {
                return null;
            }
        }
        return null;
    }

    /**
     * Days used from $start to $term with every month counting 30 (31-day months and
     * February alike): whole months x 30 + the days after them, termination day
     * included, at most 30. A termination on or before the start counts 0.
     */
    private static function thirtyDayCount(Carbon $start, Carbon $term)
    {
        if ($term->lte($start)) {
            return 0;
        }
        $dayAfter = $term->copy()->addDay();
        $months = 0;
        while ($months < self::MAX_LINES && $start->copy()->addMonthsNoOverflow($months + 1)->lte($dayAfter)) {
            $months++;
        }
        $from = $start->copy()->addMonthsNoOverflow($months);
        $extra = $from->lte($term) ? min(30, $from->diffInDays($term) + 1) : 0;
        return $months * 30 + $extra;
    }

    /** Trim from the latest line, or top up the last one, so the lines add up to $total. */
    private static function fit(array $lines, $total, $eff)
    {
        if ($total <= 0) {
            return [];
        }
        if (!$lines) {
            $lines[] = ['due_date' => $eff, 'amount' => 0.0];
        }
        $sum = 0.0;
        foreach ($lines as $l) {
            $sum += $l['amount'];
        }
        if ($sum < $total) {
            $lines[count($lines) - 1]['amount'] += $total - $sum;
        } else {
            $over = $sum - $total;
            for ($i = count($lines) - 1; $i >= 0 && $over > 0; $i--) {
                $take = min($lines[$i]['amount'], $over);
                $lines[$i]['amount'] -= $take;
                $over -= $take;
            }
        }
        $out = [];
        $sum = 0.0;
        foreach ($lines as $l) {
            $a = round($l['amount'], 3);
            if ($a > 0) {
                $out[] = ['due_date' => $l['due_date'], 'amount' => $a];
                $sum += $a;
            }
        }
        // rounding leftovers go on the last line
        $diff = round($total - $sum, 3);
        if ($diff != 0 && $out) {
            $out[count($out) - 1]['amount'] = round($out[count($out) - 1]['amount'] + $diff, 3);
        }
        return $out;
    }

    /** Receipts clear the oldest lines first. */
    private static function allocate(array $lines, $total, $received)
    {
        $left = max(0.0, $received);
        $notPaidFrom = null;
        foreach ($lines as $k => $l) {
            $paid = min($l['amount'], $left);
            $left = round($left - $paid, 3);
            $lines[$k]['unpaid'] = round($l['amount'] - $paid, 3);
            if ($notPaidFrom === null && $lines[$k]['unpaid'] > 0) {
                $notPaidFrom = $l['due_date'];
            }
        }
        return [
            'due'           => $total,
            'received'      => $received,
            'balance'       => round(max(0.0, $total - $received), 3),
            'not_paid_from' => $notPaidFrom,
            'lines'         => $lines,
        ];
    }
}
