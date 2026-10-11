<?php

namespace Modules\BackOffice\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Receivables follow-up results: what tenants paid after each follow-up.
 *
 * Rules (user, 11/10/2026):
 * - A follow-up's window runs from the follow-up up to 30 days after it, or until the next
 *   follow-up on the same agreement (by anyone), whichever comes first. Windows never overlap,
 *   so each payment is credited to one follow-up only: the latest one before it.
 * - Payments are rent receipts that are approved / posted, not cancelled and not deleted
 *   (same as RentDues). A receipt dated on the follow-up day counts when it was entered after it.
 * - Receipts of a PDC we already held (a posted PDC carries the receipt no) are not the
 *   follow-up's result: they are shown apart as "PDC cleared".
 * PHP < 7.4 in production: no arrow functions.
 */
class FollowupResults
{
    const WINDOW_DAYS = 30;

    /**
     * Follow-ups made between $from and $to (Y-m-d, inclusive), by $userIds (null = everyone),
     * each with the payments in its window.
     *
     * @return array of objects: id, contract_id, user_id, at, date, balance, window_end, open,
     *         paid (non-PDC amount), pdc (PDC amount), receipts [[no, date, amount, pdc]], cleared
     */
    public function followups($from, $to, $userIds = null, $today = null)
    {
        $today = $today ?: Carbon::today()->format('Y-m-d');
        $q = DB::table('receivable_followups')
            ->whereRaw('created_at::date between ? and ?', [$from, $to])
            ->orderBy('created_at')->orderBy('id');
        if ($userIds !== null) {
            $q->whereIn('created_by', $userIds ?: [0]);
        }
        $rows = $q->get(['id', 'tenant_contract_id', 'created_by', 'created_at', 'balance_at_followup']);
        if ($rows->isEmpty()) {
            return [];
        }
        $contractIds = $rows->pluck('tenant_contract_id')->unique()->values()->all();

        // every follow-up on these agreements from the first one on: the next one (by anyone) closes the window
        $all = DB::table('receivable_followups')->whereIn('tenant_contract_id', $contractIds)
            ->where('created_at', '>=', $rows->first()->created_at)
            ->orderBy('created_at')->orderBy('id')->get(['id', 'tenant_contract_id', 'created_at']);
        $byContract = [];
        foreach ($all as $f) {
            $byContract[$f->tenant_contract_id][] = $f;
        }

        $lastEnd = Carbon::parse($to)->addDays(self::WINDOW_DAYS)->format('Y-m-d');
        $receipts = DB::table('receipts_generation')
            ->whereIn('tenant_contract_id', $contractIds)
            ->where('receipts_generation_type', '0')->whereNull('deleted_at')
            ->whereIn('receipts_generation_approval_status', [3, 6])->where('receipts_generation_status', '<>', 2)
            ->whereBetween('receipts_generation_receipt_date', [$from, $lastEnd])
            ->orderBy('receipts_generation_receipt_date')->orderBy('created_at')
            ->get(['tenant_contract_id', 'receipts_generation_receipt_no as no', 'receipts_generation_receipt_date as rdate',
                'receipts_generation_amt as amount', 'created_at']);
        $pdcNos = [];
        $nos = $receipts->pluck('no')->filter()->unique()->values()->all();
        foreach (array_chunk($nos, 1000) as $chunk) {
            foreach (DB::table('pdc')->whereIn('pdc_receipt_no', $chunk)->where('pdc_is_posted', '1')->pluck('pdc_receipt_no') as $n) {
                $pdcNos[$n] = true;
            }
        }
        $receiptsBy = [];
        foreach ($receipts as $r) {
            $receiptsBy[$r->tenant_contract_id][] = $r;
        }

        $out = [];
        foreach ($rows as $f) {
            $next = null;
            foreach ($byContract[$f->tenant_contract_id] as $o) {
                if (self::after($o->created_at, $o->id, $f->created_at, $f->id)) {
                    $next = $o;
                    break;
                }
            }
            $date = substr($f->created_at, 0, 10);
            $end = Carbon::parse($date)->addDays(self::WINDOW_DAYS)->format('Y-m-d');
            $closedByNext = $next && substr($next->created_at, 0, 10) <= $end;
            $item = (object) [
                'id' => $f->id, 'contract_id' => (int) $f->tenant_contract_id, 'user_id' => (int) $f->created_by,
                'at' => $f->created_at, 'date' => $date,
                'balance' => $f->balance_at_followup !== null ? (float) $f->balance_at_followup : null,
                'window_end' => $closedByNext ? substr($next->created_at, 0, 10) : $end,
                'closed_by_next' => $closedByNext,
                'open' => !$closedByNext && $end >= $today,
                'paid' => 0.0, 'pdc' => 0.0, 'receipts' => [],
            ];
            $list = isset($receiptsBy[$f->tenant_contract_id]) ? $receiptsBy[$f->tenant_contract_id] : [];
            foreach ($list as $r) {
                $rdate = substr($r->rdate, 0, 10);
                // after the follow-up: a later day, or the same day but entered after it
                if ($rdate < $date || ($rdate === $date && (string) $r->created_at <= (string) $f->created_at)) {
                    continue;
                }
                if ($rdate > $end) {
                    continue;
                }
                // up to the next follow-up: an earlier day, or the same day but entered before it
                if ($closedByNext) {
                    $nd = substr($next->created_at, 0, 10);
                    if ($rdate > $nd || ($rdate === $nd && (string) $r->created_at > (string) $next->created_at)) {
                        continue;
                    }
                }
                $isPdc = isset($pdcNos[$r->no]);
                $amt = round((float) $r->amount, 3);
                if ($isPdc) {
                    $item->pdc += $amt;
                } else {
                    $item->paid += $amt;
                }
                $item->receipts[] = ['no' => $r->no, 'date' => $rdate, 'amount' => $amt, 'pdc' => $isPdc];
            }
            $item->cleared = $item->paid > 0 && $item->balance !== null && $item->paid + 0.0005 >= $item->balance;
            $out[] = $item;
        }
        return $out;
    }

    /**
     * One row per user: followups, agreements, paid_agreements, success (%), paid, cleared, pdc, last_at.
     * An agreement counts as paid when any of the user's follow-ups on it was followed by a non-PDC payment.
     */
    public static function summarise(array $followups)
    {
        $by = [];
        foreach ($followups as $f) {
            if (!isset($by[$f->user_id])) {
                $by[$f->user_id] = ['user_id' => $f->user_id, 'followups' => 0, 'agreements' => [], 'paid_agreements' => [],
                    'paid' => 0.0, 'cleared' => 0, 'pdc' => 0.0, 'open' => 0, 'last_at' => null];
            }
            $s = &$by[$f->user_id];
            $s['followups']++;
            $s['agreements'][$f->contract_id] = true;
            if ($f->paid > 0) {
                $s['paid_agreements'][$f->contract_id] = true;
            }
            $s['paid'] += $f->paid;
            $s['pdc'] += $f->pdc;
            $s['cleared'] += $f->cleared ? 1 : 0;
            $s['open'] += $f->open ? 1 : 0;
            if ($s['last_at'] === null || (string) $f->at > (string) $s['last_at']) {
                $s['last_at'] = $f->at;
            }
            unset($s);
        }
        foreach ($by as $k => $s) {
            $by[$k]['agreements'] = count($s['agreements']);
            $by[$k]['paid_agreements'] = count($s['paid_agreements']);
            $by[$k]['success'] = $by[$k]['agreements'] ? round(100 * $by[$k]['paid_agreements'] / $by[$k]['agreements']) : 0;
        }
        return array_values($by);
    }

    /** Is follow-up (at1, id1) later than (at2, id2)? */
    private static function after($at1, $id1, $at2, $id2)
    {
        $a = (string) $at1;
        $b = (string) $at2;
        return $a > $b || ($a === $b && (int) $id1 > (int) $id2);
    }
}
