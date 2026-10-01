<?php

namespace Modules\BackOffice\Http\Controllers;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\BackOffice\Entities\ReceivableFollowup;
use Modules\BackOffice\Services\ReceivableFigures;
use Modules\Masters\Entities\Building;
use Modules\Sales\Entities\TenantContract;

/**
 * Receivables Follow-up: agreements that owe rent as on a month end, with the
 * ARE's follow-ups (comment + promised date). Figures come cached from
 * ReceivableFigures; contract details and follow-ups are read live.
 * PHP < 7.4 in production: no arrow functions.
 */
class ReceivableFollowupController extends Controller
{
    const PER_PAGE = 25;
    const SORTS = ['balance', 'due', 'received', 'last_received', 'tenant', 'building', 'contract'];
    const MANAGEMENT = [1 => 'Comprehensive', 2 => 'Normal'];

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view_receivable_followup');
        $this->middleware('permission:add_receivable_followup')->only(['form', 'store']);
    }

    /** [month, year, asOn] from the request; default last month. */
    private function period(Request $request)
    {
        list($m, $y) = ReceivableFigures::lastMonth();
        $month = (int) $request->input('month', $m);
        $year = (int) $request->input('year', $y);
        if ($month < 1 || $month > 12 || $year < 2000 || $year > (int) date('Y')) {
            $month = $m;
            $year = $y;
        }
        return [$month, $year, ReceivableFigures::asOnDate($month, $year)];
    }

    public function index(Request $request)
    {
        list($month, $year) = $this->period($request);
        $buildings = Building::areFilter()->where('building_status', 1)->orderBy('building_name')->get(['id', 'building_name']);
        $years = range((int) date('Y'), 2018);
        $management = self::MANAGEMENT;
        return view('backoffice::ReceivableFollowup.index', compact('month', 'year', 'buildings', 'years', 'management'));
    }

    public function rows(Request $request)
    {
        list($month, $year, $asOn) = $this->period($request);
        $data = app(ReceivableFigures::class)->get($asOn);
        $fig = $data['rows'];

        $q = TenantContract::query()->areFilter()
            ->join('buildings', 'buildings.id', '=', 'tenant_contracts.building_id')
            ->join('tenant', 'tenant.id', '=', 'tenant_contracts.tenant_id')
            ->leftJoin('units', 'units.id', '=', 'tenant_contracts.unit_id')
            ->whereIn('tenant_contracts.id', $fig ? array_keys($fig) : [0])
            ->select('tenant_contracts.id', 'tenant_contracts.tenant_contract_no', 'tenant_contracts.tenant_contract_effective_date',
                'tenant_contracts.tenant_contract_valid_to_date', 'tenant_contracts.building_id', 'buildings.building_name',
                'buildings.management_id', 'tenant.tenant_name', 'tenant.tenant_contact_person', 'tenant.tenant_contact_no', 'units.unit_no');
        if ($request->filled('building_id')) {
            $q->where('tenant_contracts.building_id', (int) $request->building_id);
        }
        if (isset(self::MANAGEMENT[(int) $request->input('management_id')])) {
            $q->where('buildings.management_id', (int) $request->management_id);
        }
        if ($request->filled('q')) {
            $term = '%' . trim($request->q) . '%';
            $q->where(function ($w) use ($term) {
                $w->where('tenant.tenant_name', 'ilike', $term)
                  ->orWhere('tenant_contracts.tenant_contract_no', 'ilike', $term)
                  ->orWhere('units.unit_no', 'ilike', $term);
            });
        }

        $list = [];
        $totals = ['due' => 0.0, 'received' => 0.0, 'balance' => 0.0];
        foreach ($q->get() as $c) {
            $f = $fig[$c->id];
            $list[] = (object) [
                'id' => $c->id, 'contract' => $c->tenant_contract_no, 'tenant' => $c->tenant_name,
                'contact_person' => $c->tenant_contact_person, 'phone' => $c->tenant_contact_no,
                'from' => $c->tenant_contract_effective_date, 'to' => $c->tenant_contract_valid_to_date,
                'building' => $c->building_name, 'unit' => $c->unit_no,
                'due' => $f['due'], 'received' => $f['received'], 'balance' => $f['balance'], 'last_received' => $f['last_received'],
            ];
            $totals['due'] += $f['due'];
            $totals['received'] += $f['received'];
            $totals['balance'] += $f['balance'];
        }

        $sort = in_array($request->input('sort'), self::SORTS, true) ? $request->input('sort') : 'balance';
        $desc = $request->input('direction', $sort === 'balance' ? 'desc' : 'asc') === 'desc';
        usort($list, function ($a, $b) use ($sort, $desc) {
            $x = $a->$sort;
            $y = $b->$sort;
            $cmp = is_numeric($x) && is_numeric($y) ? ($x <=> $y) : strcasecmp((string) $x, (string) $y);
            if ($cmp === 0) {
                $cmp = $a->id <=> $b->id;
            }
            return $desc ? -$cmp : $cmp;
        });

        $page = max(1, (int) $request->input('page', 1));
        $paginator = new LengthAwarePaginator(array_slice($list, ($page - 1) * self::PER_PAGE, self::PER_PAGE), count($list), self::PER_PAGE, $page,
            ['path' => route('receivable-followup.rows'), 'query' => $request->except('page')]);

        $followups = ReceivableFollowup::with('creator.employee')
            ->whereIn('tenant_contract_id', collect($paginator->items())->pluck('id')->all() ?: [0])
            ->orderBy('created_at', 'desc')->orderBy('id', 'desc')->get()->groupBy('tenant_contract_id');

        return view('backoffice::ReceivableFollowup._rows', [
            'rows' => $paginator, 'totals' => $totals, 'followups' => $followups, 'computedAt' => $data['computed_at'],
            'asOn' => $asOn, 'sort' => $sort, 'desc' => $desc, 'today' => Carbon::today()->format('Y-m-d'),
        ]);
    }

    public function refresh(Request $request)
    {
        list($month, $year, $asOn) = $this->period($request);
        $data = app(ReceivableFigures::class)->refresh($asOn);
        return response()->json(['computed_at' => $data['computed_at']]);
    }
}
