<?php

namespace Modules\Maintenance\Services;

use Illuminate\Support\Facades\DB;

/**
 * Figures for the inspection dashboard and reports, limited to what the user may see.
 *   compliance  - per executive and month: monthly inspections scheduled (not cancelled) vs completed
 *   aging       - unclosed issues (incl. resolved awaiting verification) by priority and days since raised
 *   matrix      - issues raised in the period by building and category, all statuses
 *   summary     - per executive in the period: visits done, issues raised, issues closed (verified), avg days to close
 * PHP < 7.4.
 */
class InspectionReports
{
    const AGE_BUCKETS = ['0-7 days' => [0, 7], '8-15 days' => [8, 15], '16-30 days' => [16, 30], '31+ days' => [31, 100000]];

    private $executive;
    private $building;

    public function __construct($executive = null, $building = null)
    {
        $this->executive = $executive ?: null;
        $this->building = $building ?: null;
    }

    private function filter($q, $alias)
    {
        Inspection::scope($q, $alias);
        if ($this->executive) {
            $q->where($alias . '.executive_id', $this->executive);
        }
        if ($this->building) {
            $q->where($alias . '.building_id', $this->building);
        }
        return $q;
    }

    /** @return array ['months' => [Y-m => label], 'rows' => [exec_id => [Y-m => [due, done]]], 'totals' => [Y-m => [due, done]]] */
    public function compliance($fromMonth, $toMonth)
    {
        $q = $this->filter(DB::table('inspection_visits as v'), 'v')
            ->where('v.visit_type', Inspection::MONTHLY)->where('v.status', '<>', Inspection::CANCELLED)
            ->whereBetween('v.visit_month', [$fromMonth . '-01', $toMonth . '-01'])
            ->groupBy('v.executive_id', 'v.visit_month')
            ->select('v.executive_id', 'v.visit_month', DB::raw('count(*) due'), DB::raw('count(*) filter (where v.status = 2) done'))->get();
        $months = [];
        for ($m = $fromMonth; $m <= $toMonth; $m = date('Y-m', strtotime($m . '-01 +1 month'))) {
            $months[$m] = date('M Y', strtotime($m . '-01'));
        }
        $rows = [];
        $totals = [];
        foreach ($q as $r) {
            $m = substr($r->visit_month, 0, 7);
            $rows[$r->executive_id][$m] = [(int) $r->due, (int) $r->done];
            $t = isset($totals[$m]) ? $totals[$m] : [0, 0];
            $totals[$m] = [$t[0] + $r->due, $t[1] + $r->done];
        }
        return ['months' => $months, 'rows' => $rows, 'totals' => $totals];
    }

    /** @return array [priority => [bucket label => count]] */
    public function aging()
    {
        $issues = $this->filter(DB::table('inspection_issues as i'), 'i')->where('i.status', '<>', Inspection::CLOSED)
            ->select('i.priority', DB::raw('(current_date - i.raised_at::date) as age'))->get();
        $out = [];
        foreach (Inspection::PRIORITIES as $p => $label) {
            foreach (self::AGE_BUCKETS as $b => $range) {
                $out[$p][$b] = 0;
            }
        }
        foreach ($issues as $i) {
            foreach (self::AGE_BUCKETS as $b => $range) {
                if ($i->age >= $range[0] && $i->age <= $range[1]) {
                    $out[$i->priority][$b]++;
                    break;
                }
            }
        }
        return $out;
    }

    /** @return array ['categories' => [id => name], 'rows' => [building name => [work_id => n]]] */
    public function matrix($from, $to)
    {
        $rows = $this->filter(DB::table('inspection_issues as i'), 'i')->join('buildings as b', 'b.id', '=', 'i.building_id')
            ->whereBetween(DB::raw('i.raised_at::date'), [$from, $to])
            ->groupBy('b.building_name', 'i.work_id')->select('b.building_name', 'i.work_id', DB::raw('count(*) n'))
            ->orderBy('b.building_name')->get();
        $used = $rows->pluck('work_id')->unique()->all();
        $categories = DB::table('works')->whereIn('id', $used ?: [0])->orderBy('works_code')->pluck('works_code', 'id')->all();
        $out = [];
        foreach ($rows as $r) {
            $out[$r->building_name][$r->work_id] = (int) $r->n;
        }
        return ['categories' => $categories, 'rows' => $out];
    }

    /** @return array [exec_id => [visits, raised, closed, avg_days|null]] */
    public function summary($from, $to)
    {
        $visits = $this->filter(DB::table('inspection_visits as v'), 'v')->where('v.status', Inspection::COMPLETED)
            ->whereBetween(DB::raw('v.completed_at::date'), [$from, $to])
            ->groupBy('v.executive_id')->select('v.executive_id', DB::raw('count(*) n'))->pluck('n', 'executive_id')->all();
        $raised = $this->filter(DB::table('inspection_issues as i'), 'i')->whereBetween(DB::raw('i.raised_at::date'), [$from, $to])
            ->groupBy('i.executive_id')->select('i.executive_id', DB::raw('count(*) n'))->pluck('n', 'executive_id')->all();
        $closed = $this->filter(DB::table('inspection_issues as i'), 'i')->where('i.status', Inspection::CLOSED)
            ->whereBetween(DB::raw('i.closed_at::date'), [$from, $to])->groupBy('i.executive_id')
            ->select('i.executive_id', DB::raw('count(*) n'), DB::raw('avg(i.closed_at::date - i.raised_at::date) avg_days'))->get()->keyBy('executive_id');
        $out = [];
        foreach (array_unique(array_merge(array_keys($visits), array_keys($raised), $closed->keys()->all())) as $e) {
            $c = isset($closed[$e]) ? $closed[$e] : null;
            $out[$e] = [isset($visits[$e]) ? (int) $visits[$e] : 0, isset($raised[$e]) ? (int) $raised[$e] : 0,
                $c ? (int) $c->n : 0, $c ? round((float) $c->avg_days, 1) : null];
        }
        return $out;
    }
}
