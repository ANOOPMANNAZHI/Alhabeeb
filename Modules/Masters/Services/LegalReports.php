<?php

namespace Modules\Masters\Services;

use Illuminate\Support\Facades\DB;
use Modules\BackOffice\Services\RentOutstanding;

/**
 * Legal module phase 5 (plan 2026-10-13): report rows for
 *  - Legal Cases Summary: every case with handled by / type / priority / lawyer / stage, days in legal and
 *    at the stage, claim and outstanding today, settlement status and balance; summaries by stage x priority
 *    and by age in legal x priority.
 *  - Legal Settlements: every settlement with total, collected, cheques held, balance, overdue, waiver.
 */
class LegalReports
{
    const AGE_BUCKETS = ['0-30' => [0, 30], '31-90' => [31, 90], '91-180' => [91, 180], '181-365' => [181, 365], '365+' => [366, PHP_INT_MAX]];

    public static function state($row)
    {
        if ($row->closed_at) {
            return 'Closed';
        }
        $map = [801 => 'PLM approval', 802 => 'Lawyer approval', 803 => 'Active', 804 => 'Referred back'];
        return isset($map[(int) $row->work_flow_processes_code]) ? $map[(int) $row->work_flow_processes_code] : '-';
    }

    /** @param array $f status (open|closed|all), handled_by, case_type, priority, stage_id, lawyer, from, to (moved to legal) */
    public static function cases(array $f)
    {
        $q = DB::table('legal as l')
            ->join('tenant_contracts as tc', 'tc.id', '=', 'l.tenant_contract_id')
            ->leftJoin('tenant as t', 't.id', '=', 'tc.tenant_id')
            ->leftJoin('buildings as b', 'b.id', '=', 'l.building_id')
            ->leftJoin('units as u', 'u.id', '=', 'l.unit_id')
            ->leftJoin('legal_stages as s', 's.id', '=', 'l.stage_id')
            ->leftJoin('users as lw', 'lw.id', '=', 'l.lawyer_user_id')
            ->select('l.*', 'tc.tenant_contract_no', 'tc.tenant_contract_rent', 't.tenant_name', 'b.building_name', 'u.unit_code',
                's.name as stage_name', 's.sort_order as stage_order', 'lw.username as lawyer_name',
                DB::raw("(SELECT max(c.created_at) FROM legal_case_updates c WHERE c.legal_id = l.id AND c.kind = 'stage') AS stage_at"));

        $status = isset($f['status']) ? $f['status'] : 'open';
        if ($status === 'open') {
            $q->whereNull('l.closed_at');
        } elseif ($status === 'closed') {
            $q->whereNotNull('l.closed_at');
        }
        foreach (['handled_by', 'case_type', 'priority'] as $k) {
            if (!empty($f[$k])) {
                $q->where('l.' . $k, (int) $f[$k]);
            }
        }
        if (!empty($f['stage_id'])) {
            $f['stage_id'] === 'none' ? $q->whereNull('l.stage_id') : $q->where('l.stage_id', (int) $f['stage_id']);
        }
        if (!empty($f['lawyer'])) {
            $q->where('l.lawyer_user_id', (int) $f['lawyer']);
        }
        if (!empty($f['from'])) {
            $q->whereDate('l.created_at', '>=', $f['from']);
        }
        if (!empty($f['to'])) {
            $q->whereDate('l.created_at', '<=', $f['to']);
        }
        $rows = $q->orderBy('l.priority')->orderBy('l.created_at')->get();

        $today = strtotime(date('Y-m-d'));
        $settlements = DB::table('legal_settlements')->whereIn('legal_id', $rows->pluck('id')->all() ?: [0])
            ->where('status', '<>', LegalSettlement::CANCELLED)->orderBy('id')->get()->keyBy('legal_id');   // newest wins
        foreach ($rows as $r) {
            $end = $r->closed_at ? strtotime(substr($r->closed_at, 0, 10)) : $today;
            $r->state = self::state($r);
            $r->days_in_legal = (int) floor(($end - strtotime(substr($r->created_at, 0, 10))) / 86400);
            $r->days_at_stage = $r->stage_at ? (int) floor(($end - strtotime(substr($r->stage_at, 0, 10))) / 86400) : null;
            $r->outstanding = $r->closed_at ? null : RentOutstanding::amountForContract($r->tenant_contract_id);
            $r->settlement_status = null;
            $r->settlement_total = null;
            $r->settlement_balance = null;
            if (isset($settlements[$r->id])) {
                $s = $settlements[$r->id];
                $r->settlement_status = LegalSettlement::STATUS[(int) $s->status];
                $r->settlement_total = (float) $s->settled_total;
                if (in_array((int) $s->status, [LegalSettlement::ACTIVE, LegalSettlement::COMPLETED, LegalSettlement::BROKEN], true)) {
                    $r->settlement_balance = LegalSettlement::schedule($s, $r->tenant_contract_id)['balance'];
                }
            }
        }
        return $rows;
    }

    /** Open cases: stage (rows) x priority (columns) counts, and age in legal x priority. */
    public static function casesSummary($rows)
    {
        $open = $rows->filter(function ($r) { return !$r->closed_at; });
        $prio = LegalCase::PRIORITIES + [0 => 'Not set'];
        $byStage = [];
        foreach ($open->sortBy(function ($r) { return $r->stage_order === null ? -1 : $r->stage_order; }) as $r) {
            $stage = $r->stage_name ?: ($r->state === 'Active' ? 'Stage not set' : $r->state);
            if (!isset($byStage[$stage])) {
                $byStage[$stage] = array_fill_keys(array_keys($prio), 0) + ['total' => 0];
            }
            $byStage[$stage][(int) $r->priority]++;
            $byStage[$stage]['total']++;
        }
        $byAge = [];
        foreach (self::AGE_BUCKETS as $label => $range) {
            $byAge[$label] = array_fill_keys(array_keys($prio), 0) + ['total' => 0];
        }
        foreach ($open as $r) {
            foreach (self::AGE_BUCKETS as $label => $range) {
                if ($r->days_in_legal >= $range[0] && $r->days_in_legal <= $range[1]) {
                    $byAge[$label][(int) $r->priority]++;
                    $byAge[$label]['total']++;
                    break;
                }
            }
        }
        return ['priorities' => $prio, 'byStage' => $byStage, 'byAge' => $byAge, 'open' => $open->count(),
            'outstanding' => round($open->sum('outstanding'), 3)];
    }

    /** @param array $f status (number or ''), type, lawyer, from, to (agreed date) */
    public static function settlements(array $f)
    {
        $q = DB::table('legal_settlements as s')
            ->join('legal as l', 'l.id', '=', 's.legal_id')
            ->join('tenant_contracts as tc', 'tc.id', '=', 'l.tenant_contract_id')
            ->leftJoin('tenant as t', 't.id', '=', 'tc.tenant_id')
            ->leftJoin('buildings as b', 'b.id', '=', 'l.building_id')
            ->leftJoin('units as u', 'u.id', '=', 'l.unit_id')
            ->leftJoin('users as lw', 'lw.id', '=', 'l.lawyer_user_id')
            ->select('s.*', 'l.tenant_contract_id', 'l.closed_at', 'l.close_reason', 'tc.tenant_contract_no', 't.tenant_name',
                'b.building_name', 'u.unit_code', 'lw.username as lawyer_name');
        if (isset($f['status']) && $f['status'] !== '' && $f['status'] !== null) {
            $q->where('s.status', (int) $f['status']);
        } else {
            $q->where('s.status', '<>', LegalSettlement::CANCELLED);
        }
        if (!empty($f['type'])) {
            $q->where('s.settlement_type', (int) $f['type']);
        }
        if (!empty($f['lawyer'])) {
            $q->where('l.lawyer_user_id', (int) $f['lawyer']);
        }
        if (!empty($f['from'])) {
            $q->whereDate('s.agreed_date', '>=', $f['from']);
        }
        if (!empty($f['to'])) {
            $q->whereDate('s.agreed_date', '<=', $f['to']);
        }
        $rows = $q->orderBy('s.agreed_date', 'desc')->orderBy('s.id', 'desc')->get();

        foreach ($rows as $r) {
            $r->status_label = LegalSettlement::STATUS[(int) $r->status];
            $r->collected = $r->held = $r->balance = $r->overdue = 0.0;
            $r->oldest_overdue_days = 0;
            $r->next_due = null;
            if (in_array((int) $r->status, [LegalSettlement::ACTIVE, LegalSettlement::COMPLETED, LegalSettlement::BROKEN], true)) {
                $sc = LegalSettlement::schedule($r, $r->tenant_contract_id);
                $r->collected = $sc['collected'];
                $r->held = $sc['held'];
                $r->balance = $sc['balance'];
                foreach ($sc['rows'] as $i) {
                    if ($i->status === 'overdue') {
                        $r->overdue += $i->amount - $i->paid;
                        $r->oldest_overdue_days = max($r->oldest_overdue_days, $i->days_overdue);
                    } elseif (in_array($i->status, ['due', 'partly paid'], true) && !$r->next_due) {
                        $r->next_due = $i->due_date;
                    }
                }
                $r->overdue = round($r->overdue, 3);
            }
            $r->waiver_live = (float) $r->waiver_amount > 0 && !$r->waiver_cancelled_at;
        }
        return $rows;
    }

    public static function settlementTotals($rows)
    {
        $t = ['count' => $rows->count()];
        foreach (['amount', 'waiver_amount', 'fees_recovered', 'settled_total', 'collected', 'held', 'balance', 'overdue'] as $k) {
            $t[$k] = round($rows->sum(function ($r) use ($k) {
                if ($k === 'waiver_amount' && !$r->waiver_live) {
                    return 0;
                }
                return (float) $r->$k;
            }), 3);
        }
        return $t;
    }
}
