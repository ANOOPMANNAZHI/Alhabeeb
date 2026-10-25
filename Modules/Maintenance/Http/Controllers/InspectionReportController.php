<?php

namespace Modules\Maintenance\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Maintenance\Exports\InspectionReportExport;
use Modules\Maintenance\Services\Inspection;
use Modules\Maintenance\Services\InspectionReports;

/**
 * Building inspection dashboard and reports (with Excel export). PHP < 7.4.
 */
class InspectionReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:inspection_view|inspection_visit_create|inspection_execute')->only(['dashboard']);
        $this->middleware('permission:inspection_reports')->only(['reports', 'export']);
    }

    public function dashboard()
    {
        $month = date('Y-m');
        $today = date('Y-m-d');
        $visits = Inspection::scope(DB::table('inspection_visits as v'), 'v')->where('v.visit_month', $month . '-01')
            ->selectRaw('count(*) filter (where status <> 9) planned, count(*) filter (where status = 2) done,
                count(*) filter (where status = 0) pending, count(*) filter (where status = 0 and planned_date < current_date) late')->first();
        $issues = Inspection::scope(DB::table('inspection_issues as i'), 'i')
            ->selectRaw('count(*) filter (where status <> 4) open, count(*) filter (where status <> 4 and priority = 1) critical,
                count(*) filter (where status < 3 and target_date < current_date) overdue, count(*) filter (where status = 3) awaiting')->first();
        $reports = new InspectionReports();
        $compliance = $reports->compliance($month, $month);
        $attention = Inspection::scope(DB::table('inspection_issues as i'), 'i')->join('buildings as b', 'b.id', '=', 'i.building_id')
            ->where('i.status', '<', Inspection::RESOLVED)->where('i.target_date', '<', $today)
            ->select('i.id', 'i.issue_no', 'i.description', 'i.priority', 'i.target_date', 'b.building_name')
            ->orderBy('i.priority')->orderBy('i.target_date')->limit(6)->get();
        $seesAll = Inspection::seesAll();
        // managers: issues they can verify; everyone else: their own resolved issues waiting for a manager
        $awaiting = !$seesAll || \Auth::user()->can('inspection_verify')
            ? Inspection::scope(DB::table('inspection_issues as i'), 'i')->join('buildings as b', 'b.id', '=', 'i.building_id')
                ->where('i.status', Inspection::RESOLVED)->select('i.id', 'i.issue_no', 'i.description', 'i.priority', 'i.resolved_at', 'b.building_name')
                ->orderBy('i.resolved_at')->limit(6)->get()
            : collect();
        $upcoming = Inspection::scope(DB::table('inspection_visits as v'), 'v')->join('buildings as b', 'b.id', '=', 'v.building_id')
            ->where('v.status', Inspection::SCHEDULED)->where('v.planned_date', '<=', date('Y-m-d', strtotime('+14 days')))
            ->select('v.*', 'b.building_name', 'b.building_code')->orderBy('v.planned_date')->limit(8)->get();
        $names = Inspection::userNames(array_merge(array_keys($compliance['rows']), $upcoming->pluck('executive_id')->all()));
        $unassigned = $seesAll
            ? DB::table('buildings as b')->where('b.building_status', 1)
                ->whereNotExists(function ($q) { $q->select(DB::raw(1))->from('building_executives as be')->whereRaw('be.building_id = b.id')->whereNull('be.assigned_till'); })
                ->count()
            : 0;
        $myBuildings = $seesAll ? null : count(Inspection::buildingIds());
        return view('maintenance::Inspection.dashboard', compact('visits', 'issues', 'compliance', 'attention', 'awaiting', 'upcoming',
            'names', 'unassigned', 'myBuildings', 'month', 'seesAll'));
    }

    private function params(Request $request)
    {
        $to = preg_match('/^\d{4}-\d{2}$/', (string) $request->get('to')) ? $request->get('to') : date('Y-m');
        $from = preg_match('/^\d{4}-\d{2}$/', (string) $request->get('from')) ? $request->get('from') : date('Y-m', strtotime($to . '-01 -1 month'));
        if ($from > $to) {
            list($from, $to) = [$to, $from];
        }
        return [$from, $to, $request->get('executive') ?: null, $request->get('building') ?: null];
    }

    private function data(Request $request)
    {
        list($from, $to, $executive, $building) = $this->params($request);
        $reports = new InspectionReports($executive, $building);
        $fromDate = $from . '-01';
        $toDate = date('Y-m-t', strtotime($to . '-01'));
        $compliance = $reports->compliance($from, $to);
        $aging = $reports->aging();
        $matrix = $reports->matrix($fromDate, $toDate);
        $summary = $reports->summary($fromDate, $toDate);
        $names = Inspection::userNames(array_merge(array_keys($compliance['rows']), array_keys($summary)));
        $period = date('F Y', strtotime($fromDate)) . ($from === $to ? '' : ' - ' . date('F Y', strtotime($toDate)));
        return compact('from', 'to', 'executive', 'building', 'compliance', 'aging', 'matrix', 'summary', 'names', 'period');
    }

    public function reports(Request $request)
    {
        $data = $this->data($request);
        // the report holds only the user's own work unless they see everything
        $data['executives'] = Inspection::seesAll() ? Inspection::executives() : [\Auth::id() => Inspection::userName(\Auth::id())];
        $data['buildings'] = Inspection::buildings();
        $data['months'] = [];
        for ($i = 0; $i < 24; $i++) {
            $m = date('Y-m', strtotime(date('Y-m-01') . " -$i months"));
            $data['months'][$m] = date('M Y', strtotime($m . '-01'));
        }
        return view('maintenance::Inspection.reports', $data);
    }

    public function export(Request $request)
    {
        $data = $this->data($request);
        return \Excel::download(new InspectionReportExport($data), 'Inspection_Reports_' . $data['from'] . '_' . $data['to'] . '.xlsx');
    }
}
