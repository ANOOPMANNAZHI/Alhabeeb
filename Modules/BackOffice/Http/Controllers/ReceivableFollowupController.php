<?php

namespace Modules\BackOffice\Http\Controllers;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\BackOffice\Entities\ReceiptsGeneration;
use Modules\BackOffice\Entities\ReceivableFollowup;
use Modules\BackOffice\Services\FiguresBusy;
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
    const SORTS = ['balance', 'due', 'received', 'last_received', 'paid_till', 'rent', 'tenant', 'building', 'contract',
        'unit', 'are', 'last_followup', 'next_followup'];
    const MANAGEMENT = [1 => 'Comprehensive', 2 => 'Normal'];
    const FOLLOWUP_STATUS = ['none' => 'Not followed up', 'overdue' => 'Next follow-up overdue', 'today' => 'Next follow-up today',
        'upcoming' => 'Next follow-up upcoming', 'no_date' => 'Followed up, no next date'];
    // these see every building whatever other roles they hold
    const MANAGER_ROLES = ['super_admin', 'backoffice_manager', 'ceo', 'md', 'finance_manager'];

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view_receivable_followup');
        $this->middleware('permission:add_receivable_followup')->only(['form', 'store']);
    }

    /**
     * The ARE users whose buildings this user follows up: null = every building (managers and other
     * back-office users); an ARE team lead = their team + themselves; any other ARE = themselves,
     * also when they hold a second role (call centre, back-office executive).
     */
    private function scopeAreUsers()
    {
        $u = \Auth::user();
        if ($u->hasAnyRole(self::MANAGER_ROLES)) {
            return null;
        }
        if ($u->hasRole('are_team_lead')) {
            return DB::table('users')->join('employees', 'employees.id', '=', 'users.user_type_id')
                ->where('users.user_type', 'employee')->where('employees.head_user', $u->id)
                ->pluck('users.id')->push($u->id)->unique()->values()->all();
        }
        return $u->hasRole('are') ? [$u->id] : null;
    }

    /** Building ids this user may see (null = all). */
    private function scopeBuildingIds()
    {
        $users = $this->scopeAreUsers();
        if ($users === null) {
            return null;
        }
        return DB::table('preferred_buildings as pb')->join('are_buildings as ab', 'ab.id', '=', 'pb.are_building_id')
            ->whereNull('pb.assign_to')->whereIn('ab.user_id', $users ?: [0])
            ->distinct()->pluck('pb.building_id')->map(function ($v) { return (int) $v; })->all();
    }

    /** Current ARE assignments: building_id, user_id, name. */
    private function areAssignments()
    {
        return DB::table('preferred_buildings as pb')->join('are_buildings as ab', 'ab.id', '=', 'pb.are_building_id')
            ->join('users as u', 'u.id', '=', 'ab.user_id')->leftJoin('employees as e', 'e.id', '=', 'u.user_type_id')
            ->whereNull('pb.assign_to')
            ->select('pb.building_id', 'ab.user_id', DB::raw('coalesce(e.employee_name, u.username) as name'))
            ->distinct()->orderBy('name')->get();
    }

    /** An ARE following up only their own buildings has no use for the ARE column / filter. */
    private function showAre()
    {
        $users = $this->scopeAreUsers();
        return $users === null || count($users) > 1;
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
        $scope = $this->scopeBuildingIds();
        $bq = Building::where('building_status', 1)->whereNotIn('id', ReceivableFigures::EXCLUDED_BUILDINGS);
        if ($scope !== null) {
            $bq->whereIn('id', $scope ?: [0]);
        }
        $buildings = $bq->orderBy('building_name')->get(['id', 'building_name']);
        $years = range((int) date('Y'), 2018);
        $management = self::MANAGEMENT;
        $followupStatus = self::FOLLOWUP_STATUS;
        $showAre = $this->showAre();
        $ares = [];
        if ($showAre) {
            foreach ($this->areAssignments() as $a) {
                if ($scope === null || in_array((int) $a->building_id, $scope, true)) {
                    $ares[$a->user_id] = $a->name;
                }
            }
            asort($ares);
        }
        return view('backoffice::ReceivableFollowup.index', compact('month', 'year', 'buildings', 'years', 'management', 'followupStatus', 'showAre', 'ares'));
    }

    public function rows(Request $request)
    {
        list($month, $year, $asOn) = $this->period($request);
        try {
            $data = app(ReceivableFigures::class)->get($asOn);
        } catch (FiguresBusy $e) {
            return response()->json(['busy' => true], 202);
        }
        // every agreement still owing at the cutoff (picked month - 2), see ReceivableFigures
        $fig = $data['rows'];

        $scope = $this->scopeBuildingIds();
        $showAre = $this->showAre();
        $areByBuilding = [];   // building id => [names]
        $areBuildings = [];    // ARE user id => [building ids]
        foreach ($this->areAssignments() as $a) {
            $areByBuilding[$a->building_id][] = $a->name;
            $areBuildings[$a->user_id][] = (int) $a->building_id;
        }

        $q = TenantContract::query()
            ->join('buildings', 'buildings.id', '=', 'tenant_contracts.building_id')
            ->join('tenant', 'tenant.id', '=', 'tenant_contracts.tenant_id')
            ->leftJoin('units', 'units.id', '=', 'tenant_contracts.unit_id')
            ->whereIn('tenant_contracts.id', $fig ? array_keys($fig) : [0])
            ->select('tenant_contracts.id', 'tenant_contracts.tenant_contract_no', 'tenant_contracts.tenant_contract_effective_date',
                'tenant_contracts.tenant_contract_valid_to_date', 'tenant_contracts.building_id', 'buildings.building_name',
                'buildings.management_id', 'tenant.tenant_name', 'tenant.tenant_contact_person', 'tenant.tenant_contact_no', 'units.unit_no');
        if ($scope !== null) {
            $q->whereIn('tenant_contracts.building_id', $scope ?: [0]);
        }
        if ($showAre && $request->filled('are_id')) {
            $aid = (int) $request->are_id;
            $q->whereIn('tenant_contracts.building_id', isset($areBuildings[$aid]) ? $areBuildings[$aid] : [0]);
        }
        foreach (['tenant' => 'tenant.tenant_name', 'contract' => 'tenant_contracts.tenant_contract_no', 'unit' => 'units.unit_no'] as $key => $col) {
            if ($request->filled($key)) {
                $q->where($col, 'ilike', '%' . trim($request->input($key)) . '%');
            }
        }
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

        $contracts = $q->get();
        // latest follow-up and latest next follow-up date of every listed agreement, for sorting / filtering
        $ids = $contracts->pluck('id')->all() ?: [0];
        $lastAt = DB::table('receivable_followups')->whereIn('tenant_contract_id', $ids)
            ->selectRaw('DISTINCT ON (tenant_contract_id) tenant_contract_id, created_at')
            ->orderBy('tenant_contract_id')->orderBy('created_at', 'desc')->orderBy('id', 'desc')
            ->pluck('created_at', 'tenant_contract_id')->all();
        $nextOn = DB::table('receivable_followups')->whereIn('tenant_contract_id', $ids)->whereNotNull('promised_date')
            ->selectRaw('DISTINCT ON (tenant_contract_id) tenant_contract_id, promised_date')
            ->orderBy('tenant_contract_id')->orderBy('created_at', 'desc')->orderBy('id', 'desc')
            ->pluck('promised_date', 'tenant_contract_id')->all();
        $today = Carbon::today()->format('Y-m-d');
        $status = (string) $request->input('followup');
        $balMin = is_numeric($request->input('balance_min')) ? (float) $request->balance_min : null;
        $balMax = is_numeric($request->input('balance_max')) ? (float) $request->balance_max : null;
        $paidTo = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $request->input('paid_till_to')) ? $request->paid_till_to : null;

        $list = [];
        $totals = ['due' => 0.0, 'received' => 0.0, 'balance' => 0.0];
        foreach ($contracts as $c) {
            $f = $fig[$c->id];
            $last = isset($lastAt[$c->id]) ? substr($lastAt[$c->id], 0, 19) : null;
            $next = isset($nextOn[$c->id]) ? substr($nextOn[$c->id], 0, 10) : null;
            if (($status === 'none' && $last)
                || ($status === 'overdue' && !($next && $next < $today))
                || ($status === 'today' && $next !== $today)
                || ($status === 'upcoming' && !($next && $next > $today))
                || ($status === 'no_date' && !($last && !$next))
                || ($balMin !== null && $f['balance'] < $balMin)
                || ($balMax !== null && $f['balance'] > $balMax)
                // "paid till up to": never-paid agreements have paid nothing, so they match
                || ($paidTo && !empty($f['paid_till']) && $f['paid_till'] > $paidTo)) {
                continue;
            }
            $list[] = (object) [
                'id' => $c->id, 'contract' => $c->tenant_contract_no, 'tenant' => $c->tenant_name,
                'contact_person' => $c->tenant_contact_person, 'phone' => $c->tenant_contact_no,
                'from' => $c->tenant_contract_effective_date, 'to' => $c->tenant_contract_valid_to_date,
                'building' => $c->building_name, 'unit' => $c->unit_no,
                'due' => $f['due'], 'received' => $f['received'], 'balance' => $f['balance'], 'last_received' => $f['last_received'],
                'paid_till' => isset($f['paid_till']) ? $f['paid_till'] : null,
                'not_paid_from' => isset($f['not_paid_from']) ? $f['not_paid_from'] : null,
                'last_amount' => isset($f['last_amount']) ? $f['last_amount'] : null,
                'start' => isset($f['start']) ? $f['start'] : null,
                'rent' => isset($f['rent']) ? $f['rent'] : null,
                'are' => isset($areByBuilding[$c->building_id]) ? implode(', ', array_unique($areByBuilding[$c->building_id])) : '',
                'last_followup' => $last, 'next_followup' => $next,
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
            'asOn' => $asOn, 'sort' => $sort, 'desc' => $desc, 'today' => $today, 'showAre' => $showAre,
            'monthLabel' => Carbon::create($year, $month, 1)->format('F Y'),
        ]);
    }

    public function refresh(Request $request)
    {
        list($month, $year, $asOn) = $this->period($request);
        try {
            $data = app(ReceivableFigures::class)->refresh($asOn);
        } catch (FiguresBusy $e) {
            return response()->json(['busy' => true], 202);
        }
        return response()->json(['computed_at' => $data['computed_at']]);
    }

    /** The contract, if it exists and is inside the user's ARE scope; 404 / 403 otherwise. */
    private function contractInScope($id)
    {
        $contract = TenantContract::with(['tenant', 'building', 'Unit'])->findOrFail($id);
        $scope = $this->scopeBuildingIds();
        if ($scope !== null && !in_array((int) $contract->building_id, $scope, true)) {
            abort(403);
        }
        return $contract;
    }

    /** Header shared by the form and the timeline. */
    private function header(TenantContract $contract, $asOn)
    {
        $cached = $asOn ? app(ReceivableFigures::class)->cached($asOn) : null;
        $fig = $cached && isset($cached['rows'][$contract->id]) ? $cached['rows'][$contract->id] : null;
        $lastPromise = ReceivableFollowup::where('tenant_contract_id', $contract->id)->whereNotNull('promised_date')
            ->orderBy('created_at', 'desc')->orderBy('id', 'desc')->first();
        $paidTill = $fig && !empty($fig['paid_till']) ? Carbon::parse($fig['paid_till']) : null;
        return [
            'contract'     => $contract,
            'balance'      => $fig ? $fig['balance'] : null,
            'paidTill'     => $paidTill ? $paidTill->format('d/m/Y') : null,
            'shortPaid'    => $paidTill && $fig && !empty($fig['not_paid_from']) && $paidTill->format('Y-m-d') >= $fig['not_paid_from'],
            'lastPayment'  => $fig && !empty($fig['last_received'])
                ? Carbon::parse($fig['last_received'])->format('d/m/Y') . (isset($fig['last_amount']) && $fig['last_amount'] !== null ? ' · ' . ReceivableFollowup::amount($fig['last_amount']) : '')
                : null,
            'lastPromise'  => $lastPromise ? $lastPromise->promised_date->format('d/m/Y') : null,
            'asOn'         => $asOn,
        ];
    }

    private function asOnParam(Request $request)
    {
        $v = (string) $request->input('as_on', '');
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $v) ? $v : null;
    }

    public function form(Request $request, $contract)
    {
        $c = $this->contractInScope($contract);
        // the last few calls, so the ARE sees what was said before phoning again
        $recent = ReceivableFollowup::with('creator.employee')->where('tenant_contract_id', $c->id)
            ->orderBy('created_at', 'desc')->orderBy('id', 'desc')->limit(3)->get();
        return view('backoffice::ReceivableFollowup._form', $this->header($c, $this->asOnParam($request)) + compact('recent'));
    }

    public function store(Request $request, $contract)
    {
        $c = $this->contractInScope($contract);
        $this->validate($request, [
            'comment'       => 'required|string|max:1000',
            'promised_date' => 'nullable|date|after_or_equal:today',
        ], [], ['promised_date' => 'next follow-up date']);
        $h = $this->header($c, $this->asOnParam($request));
        $f = ReceivableFollowup::create([
            'tenant_contract_id'  => $c->id,
            'comment'             => trim($request->comment),
            'promised_date'       => $request->filled('promised_date') ? $request->promised_date : null,
            'balance_at_followup' => $h['balance'],
            'created_by'          => \Auth::user()->id,
        ]);
        $f->load('creator.employee');

        $promise = ReceivableFollowup::where('tenant_contract_id', $c->id)->whereNotNull('promised_date')
            ->orderBy('created_at', 'desc')->orderBy('id', 'desc')->first();
        return response()->json([
            'last'     => $f->created_at->format('d/m/Y H:i'),
            'by'       => $f->creatorName(),
            'promised' => $promise ? $promise->promised_date->format('d/m/Y') : null,
            'overdue'  => $promise ? $promise->promised_date->format('Y-m-d') < Carbon::today()->format('Y-m-d') : false,
        ]);
    }

    public function timeline(Request $request, $contract)
    {
        $c = $this->contractInScope($contract);
        $followups = ReceivableFollowup::with('creator.employee')->where('tenant_contract_id', $c->id)
            ->orderBy('created_at', 'desc')->orderBy('id', 'desc')->get();
        $receiptDates = ReceiptsGeneration::where('tenant_contract_id', $c->id)->where('receipts_generation_type', 0)
            ->where(function ($q) { $q->whereNull('receipts_generation_status')->orWhere('receipts_generation_status', '!=', 2); })
            ->whereNotNull('receipts_generation_receipt_date')->pluck('receipts_generation_receipt_date')
            ->map(function ($d) { return Carbon::parse($d)->format('Y-m-d'); })->all();
        $today = Carbon::today()->format('Y-m-d');
        $statusFor = function ($f) use ($receiptDates, $today) {
            return ReceivableFollowup::promiseStatus($f->promised_date, $f->created_at, $receiptDates, $today);
        };
        return view('backoffice::ReceivableFollowup._timeline', $this->header($c, $this->asOnParam($request)) + compact('followups', 'statusFor'));
    }
}
