<?php

namespace Modules\BackOffice\Services;

use Modules\BackOffice\Services\TerminationDuesCategory as Cat;
use Modules\BackOffice\Entities\TerminationDues;

/**
 * Applies settlements (rent receipts, general-receipt lines, deposit-refund
 * deductions) to the owed lines of a dues record and reports balances.
 *
 * Nothing here is persisted: the caller gathers live sources each time, so
 * a cancelled, bounced or deleted receipt simply stops appearing. Only the
 * manual decisions passed in $manual come from the database.
 *
 * Allocation: a source pinned by a manual allocation goes to that line. Every
 * other source goes to its category, oldest source first, oldest line first,
 * until each line's balance is zero; what is left over in a category is
 * reported as over-collected. Sources without a category, or whose category
 * has no line, are reported as unallocated for a person to assign.
 *
 * Negative lines (discounts) are credits: they reduce the room of the positive
 * lines in the same category before any source is applied, so balances and
 * status are net of the discount. They are returned with 'credit' => true.
 */
class TerminationDuesSettlement
{
    const EPS = 0.005;

    public static function key($kind, $id)
    {
        return $kind . ':' . (int) $id;
    }

    public static function compute(array $lines, array $sources, array $manual)
    {
        $state = [];
        $byCategory = [];
        foreach ($lines as $l) {
            $id = (int) $l['id'];
            $state[$id] = [
                'id'          => $id,
                'category'    => $l['category'],
                'owner_team'  => $l['owner_team'],
                'description' => $l['description'],
                'owed'        => round((float) $l['amount'], 3),
                'deposit'     => 0.0,
                'receipts'    => 0.0,
                'waived'      => 0.0,
                'credit'      => false,
                'credit_applied' => 0.0,
            ];
            if ($state[$id]['owed'] > 0) {
                $byCategory[$l['category']][] = $id;
            }
        }

        // Negative lines (discounts) are credits against their category: fold
        // each one into the positive lines of the same category, consuming the
        // room of the LAST positive line backwards, so FIFO allocation,
        // balances and status all use net figures. The credit line itself is
        // kept for display (owed = its negative amount, balance 0, credit =
        // true). Its negative `owed` still enters the team/total owed sums, so
        // the positive lines keep their gross `owed` and carry the credit in
        // `credit_applied` (subtracted in balance()) — that way the owed
        // column ties out with the team figure. Credit beyond the category's
        // positive total is dropped (the Builder already caps discounts).
        foreach ($state as $id => $l) {
            if ($l['owed'] >= 0) {
                continue;
            }
            $state[$id]['credit'] = true;
            $remainingCredit = -$l['owed'];
            $targets = isset($byCategory[$l['category']]) ? array_reverse($byCategory[$l['category']]) : [];
            foreach ($targets as $targetId) {
                if ($remainingCredit <= self::EPS) {
                    break;
                }
                $room = self::balance($state[$targetId]);
                if ($room <= self::EPS) {
                    continue;
                }
                $take = min($room, $remainingCredit);
                $state[$targetId]['credit_applied'] += $take;
                $remainingCredit -= $take;
            }
        }

        $allocations = [];
        $pinned = []; // source key => [line_id, amount]
        foreach ($manual as $m) {
            $lineId = (int) $m['termination_dues_line_id'];
            if (!isset($state[$lineId])) {
                continue;
            }
            $amount = round((float) $m['amount'], 3);
            if ($m['source_type'] === 'waiver') {
                $state[$lineId]['waived'] += $amount;
                $allocations[] = ['source_key' => 'waiver:' . (int) $m['id'], 'line_id' => $lineId, 'amount' => $amount, 'manual' => true];
                continue;
            }
            $pinned[self::key($m['source_type'], $m['source_id'])] = ['line_id' => $lineId, 'amount' => $amount];
        }

        usort($sources, function ($a, $b) {
            $c = strcmp((string) $a['date'], (string) $b['date']);
            return $c !== 0 ? $c : ((int) $a['id'] - (int) $b['id']);
        });

        $unallocated = [];
        $over = [];
        foreach ($sources as $s) {
            $column = $s['kind'] === 'deposit_deduction' ? 'deposit' : 'receipts';
            $amount = round((float) $s['amount'], 3);

            if (isset($pinned[$s['key']])) {
                $p = $pinned[$s['key']];
                $state[$p['line_id']][$column] += $p['amount'];
                $allocations[] = ['source_key' => $s['key'], 'line_id' => $p['line_id'], 'amount' => $p['amount'], 'manual' => true];
                continue;
            }

            $category = isset($s['category']) ? $s['category'] : null;
            if ($category === null || empty($byCategory[$category])) {
                $unallocated[] = $s;
                continue;
            }

            $remaining = $amount;
            foreach ($byCategory[$category] as $lineId) {
                if ($remaining <= self::EPS) {
                    break;
                }
                $room = self::balance($state[$lineId]);
                if ($room <= self::EPS) {
                    continue;
                }
                $take = min($room, $remaining);
                $state[$lineId][$column] += $take;
                $allocations[] = ['source_key' => $s['key'], 'line_id' => $lineId, 'amount' => round($take, 3), 'manual' => false];
                $remaining -= $take;
            }
            if ($remaining > self::EPS) {
                $over[$category] = round((isset($over[$category]) ? $over[$category] : 0) + $remaining, 3);
            }
        }

        $teams = [
            Cat::TEAM_BACKOFFICE  => ['owed' => 0.0, 'settled' => 0.0, 'balance' => 0.0],
            Cat::TEAM_MAINTENANCE => ['owed' => 0.0, 'settled' => 0.0, 'balance' => 0.0],
        ];
        $total = ['owed' => 0.0, 'settled' => 0.0, 'waived' => 0.0, 'balance' => 0.0];
        foreach ($state as $id => &$l) {
            $l['settled'] = round($l['deposit'] + $l['receipts'], 3);
            $l['balance'] = $l['credit'] ? 0.0 : round(max(self::balance($l), 0), 3);
            $l['credit_applied'] = round($l['credit_applied'], 3);
            $l['deposit'] = round($l['deposit'], 3);
            $l['receipts'] = round($l['receipts'], 3);
            $l['waived'] = round($l['waived'], 3);

            $team = isset($teams[$l['owner_team']]) ? $l['owner_team'] : Cat::TEAM_BACKOFFICE;
            $teams[$team]['owed'] += $l['owed'];
            $teams[$team]['settled'] += $l['settled'];
            $teams[$team]['balance'] += $l['balance'];
            $total['owed'] += $l['owed'];
            $total['settled'] += $l['settled'];
            $total['waived'] += $l['waived'];
            $total['balance'] += $l['balance'];
        }
        unset($l);
        foreach ($teams as &$t) {
            $t = array_map(function ($v) { return round($v, 3); }, $t);
        }
        unset($t);
        $total = array_map(function ($v) { return round($v, 3); }, $total);

        return [
            'lines'          => $state,
            'teams'          => $teams,
            'total'          => $total,
            'unallocated'    => $unallocated,
            'over_collected' => $over,
            'allocations'    => $allocations,
            'status'         => self::status($total),
        ];
    }

    private static function balance(array $line)
    {
        return $line['owed'] - $line['credit_applied'] - $line['deposit'] - $line['receipts'] - $line['waived'];
    }

    private static function status(array $total)
    {
        if ($total['balance'] <= self::EPS) {
            return $total['waived'] > self::EPS ? TerminationDues::STATUS_WRITTEN_OFF : TerminationDues::STATUS_SETTLED;
        }
        return ($total['settled'] + $total['waived']) > self::EPS ? TerminationDues::STATUS_PARTIAL : TerminationDues::STATUS_OPEN;
    }
}
