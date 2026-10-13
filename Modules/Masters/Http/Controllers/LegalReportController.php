<?php

namespace Modules\Masters\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Masters\Exports\LegalReportExport;
use Modules\Masters\Services\LegalCase;
use Modules\Masters\Services\LegalReports;
use Modules\Masters\Services\LegalSettlement;

/** Legal reports (plan 2026-10-13, phase 5): Legal Cases Summary and Legal Settlements, screen / PDF / Excel. */
class LegalReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function cases(Request $request)
    {
        abort_unless(\Auth::user()->can('view_legal_cases_summary'), 403);
        $filters = $request->only('status', 'handled_by', 'case_type', 'priority', 'stage_id', 'lawyer', 'from', 'to') + ['status' => 'open'];
        $rows = LegalReports::cases($filters);
        $data = [
            'rows' => $rows,
            'summary' => LegalReports::casesSummary($rows),
            'filters' => $filters,
            'stages' => DB::table('legal_stages')->where('is_final', false)->orderBy('sort_order')->get(),
            'lawyers' => LegalCase::lawyers(),
            'generatedAt' => date('d/m/Y H:i'),
            'user' => \Auth::user()->username,
        ];
        return $this->respond($request, 'cases', $data, 'Legal Cases', 'legal_cases_summary');
    }

    public function settlements(Request $request)
    {
        abort_unless(\Auth::user()->can('view_legal_settlements_report'), 403);
        $filters = $request->only('status', 'type', 'lawyer', 'from', 'to');
        $rows = LegalReports::settlements($filters);
        $data = [
            'rows' => $rows,
            'totals' => LegalReports::settlementTotals($rows),
            'filters' => $filters,
            'lawyers' => LegalCase::lawyers(),
            'generatedAt' => date('d/m/Y H:i'),
            'user' => \Auth::user()->username,
        ];
        return $this->respond($request, 'settlements', $data, 'Legal Settlements', 'legal_settlements');
    }

    private function respond(Request $request, $name, array $data, $title, $file)
    {
        $export = $request->input('export');
        if ($export === 'pdf' || $export === 'excel') {
            // all open cases is ~600 rows x 19 columns: dompdf needs more than the default 128M
            $this->raiseMemoryLimit('512M');
            set_time_limit(300);
        }
        if ($export === 'pdf') {
            $data['pdf'] = true;
            return \PDF::loadView('masters::Legal.reports.' . $name . '_export', $data)->setPaper('a4', 'landscape')
                ->download($file . '_' . date('Y-m-d') . '.pdf');
        }
        if ($export === 'excel') {
            $data['pdf'] = false;
            return \Excel::download(new LegalReportExport('masters::Legal.reports.' . $name . '_export', $data, $title),
                $file . '_' . date('Y-m-d') . '.xlsx');
        }
        return view('masters::Legal.reports.' . $name, $data);
    }

    /** Raise memory_limit to $want only when the current limit is lower ('-1' = unlimited stays). */
    private function raiseMemoryLimit($want)
    {
        $toBytes = function ($v) {
            $v = trim((string) $v);
            $n = (float) $v;
            switch (strtolower(substr($v, -1))) {
                case 'g': $n *= 1024;
                case 'm': $n *= 1024;
                case 'k': $n *= 1024;
            }
            return $n;
        };
        $cur = ini_get('memory_limit');
        if ($cur !== '-1' && $toBytes($cur) < $toBytes($want)) {
            ini_set('memory_limit', $want);
        }
    }
}
