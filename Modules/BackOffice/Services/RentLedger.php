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
 *    date, else days x rent / 30 (days = termination - start - 1, half to even,
 *    as Receivable Report v2); a termination before the start owes nothing;
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

    public static function build($c, $asOn, $received)
    {
        $asOn = substr((string) $asOn, 0, 10);
        $eff = substr((string) $c->eff, 0, 10);
        $received = round((float) $received, 3);
        if ($eff === '' || $eff > $asOn) {
            return self::allocate([], 0.0, $received);
        }
        $rent = (float) $c->rent;
        $value = (float) $c->value;
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
                $start = Carbon::parse(!empty($c->start) ? $c->start : $eff)->startOfDay();
                $days = $start->diffInDays(Carbon::parse($term)->startOfDay(), false) - 1;
                $total = $days > 0 ? (float) round($days * ($rent / 30), 0, PHP_ROUND_HALF_EVEN) : 0.0;
            }
        } elseif ($vto !== null && $vto <= $asOn) {
            $total = $value > 0 ? $value : $sum;
        } else {
            $total = $value > 0 ? min($sum, $value) : $sum;
        }
        $total = round($total, 3);

        return self::allocate(self::fit($lines, $total, $eff), $total, $received);
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
