<?php

namespace Modules\Maintenance\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Maintenance\Services\Inspection;

/**
 * Building inspection visits: schedule, cancel (with reason), carry out the inspection, view the result.
 * Only the visit's executive carries it out; managers / supervisors schedule for any executive.
 * PHP < 7.4: no arrow functions.
 */
class InspectionVisitController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:inspection_view|inspection_visit_create|inspection_execute');
        $this->middleware('permission:inspection_visit_create')->only(['store']);
        $this->middleware('permission:inspection_execute')->only(['inspect', 'submit']);
    }

    public function index(Request $request)
    {
        $user = \Auth::user();
        $seesAll = Inspection::seesAll();
        $canExecute = $user->can('inspection_execute');
        $tab = $request->get('tab', $canExecute ? 'mine' : 'all');
        $month = preg_match('/^\d{4}-\d{2}$/', (string) $request->get('month')) ? $request->get('month') : date('Y-m');
        $f = ['executive' => $request->get('executive'), 'status' => $request->get('status'), 'building' => $request->get('building'),
            'type' => $request->get('type')];

        $q = DB::table('inspection_visits as v')->join('buildings as b', 'b.id', '=', 'v.building_id')
            ->select('v.*', 'b.building_name', 'b.building_code');
        if ($tab === 'mine') {
            // my schedule: everything still to do, plus this month's done / cancelled
            $q->where('v.executive_id', $user->id)->where(function ($w) {
                $w->where('v.status', Inspection::SCHEDULED)->orWhere('v.planned_date', '>=', date('Y-m-01'));
            })->orderBy('v.created_at', 'desc')->orderBy('v.id', 'desc'); // newest first (user, 18/10/2026)
        } else {
            Inspection::scope($q, 'v');
            $q->where('v.visit_month', $month . '-01');
            if ($f['executive']) {
                $q->where('v.executive_id', $f['executive']);
            }
            if ($f['status'] !== null && $f['status'] !== '') {
                $q->where('v.status', $f['status']);
            }
            if ($f['building']) {
                $q->where('v.building_id', $f['building']);
            }
            if ($f['type']) {
                $q->where('v.visit_type', $f['type']);
            }
            $q->orderBy('v.created_at', 'desc')->orderBy('v.id', 'desc');
        }
        $visits = $q->get();
        $names = Inspection::userNames($visits->pluck('executive_id')->all());
        $issueCounts = DB::table('inspection_issues')->whereIn('visit_id', $visits->pluck('id')->all() ?: [0])
            ->groupBy('visit_id')->select('visit_id', DB::raw('count(*) n'))->pluck('n', 'visit_id')->all();

        $buildings = Inspection::buildings();
        $executives = Inspection::executives();
        $buildingExec = DB::table('building_executives')->whereNull('assigned_till')->pluck('user_id', 'building_id')->all();
        $months = [];
        for ($i = -2; $i <= 12; $i++) {
            $m = date('Y-m', strtotime(date('Y-m-01') . " -$i months"));
            $months[$m] = date('F Y', strtotime($m . '-01'));
        }
        return view('maintenance::Inspection.visits', compact('tab', 'month', 'months', 'f', 'visits', 'names', 'issueCounts',
            'buildings', 'executives', 'buildingExec', 'seesAll', 'canExecute'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'building_id'  => 'required|integer',
            'visit_type'   => 'required|in:1,2,3',
            'planned_date' => 'required|date_format:Y-m-d',
            'purpose'      => 'nullable|string|max:2000',
            'executive_id' => 'nullable|integer',
        ], ['building_id.required' => 'Select a building.', 'planned_date.required' => 'Enter the planned visit date.']);
        $user = \Auth::user();
        $buildingId = (int) $request->input('building_id');
        if (!array_key_exists($buildingId, Inspection::buildings())) {
            return back()->withInput()->withErrors(['building_id' => 'Select one of your buildings.']);
        }
        if (Inspection::seesAll()) {
            $executiveId = (int) $request->input('executive_id');
            if (!array_key_exists($executiveId, Inspection::executives())) {
                return back()->withInput()->withErrors(['executive_id' => 'Select the executive who will carry out the visit.']);
            }
        } else {
            $executiveId = $user->id; // an executive schedules only for themselves
        }
        $planned = $request->input('planned_date');
        $month = date('Y-m-01', strtotime($planned));
        $type = (int) $request->input('visit_type');
        if ($type === Inspection::MONTHLY && $this->monthlyExists($buildingId, $month)) {
            return back()->withInput()->withErrors(['visit_type' => 'This building already has a monthly inspection for ' . date('F Y', strtotime($month))
                . '. Choose Follow-up or Additional inspection, or use the existing visit.']);
        }
        $id = DB::table('inspection_visits')->insertGetId([
            'building_id' => $buildingId, 'executive_id' => $executiveId, 'visit_type' => $type, 'visit_month' => $month,
            'planned_date' => $planned, 'purpose' => $request->input('purpose'), 'status' => Inspection::SCHEDULED,
            'created_by' => $user->id, 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('inspection_visits')->where('id', $id)->update(['visit_no' => 'VIS-' . date('y') . '-' . str_pad($id, 5, '0', STR_PAD_LEFT)]);
        Inspection::syncAccess($executiveId);
        return redirect()->route('inspection.visits', ['tab' => $executiveId === $user->id ? 'mine' : 'all', 'month' => substr($month, 0, 7)])
            ->with('success', 'Visit scheduled for ' . Inspection::userName($executiveId) . ' on ' . date('d/m/Y', strtotime($planned)) . '.');
    }

    private function monthlyExists($buildingId, $month)
    {
        return DB::table('inspection_visits')->where('building_id', $buildingId)->where('visit_month', $month)
            ->where('visit_type', Inspection::MONTHLY)->where('status', '<>', Inspection::CANCELLED)->exists();
    }

    public function cancel(Request $request, $id)
    {
        $request->validate(['cancel_reason' => 'required|string|min:10|max:1000'],
            ['cancel_reason.required' => 'Enter the reason for cancelling.', 'cancel_reason.min' => 'Give a clear reason (at least 10 characters).']);
        $visit = DB::table('inspection_visits')->where('id', $id)->first();
        abort_unless($visit, 404);
        if ((int) $visit->executive_id !== (int) \Auth::id() && !Inspection::seesAll()) {
            abort(403);
        }
        if ((int) $visit->status !== Inspection::SCHEDULED) {
            return back()->with('error', 'Only a scheduled visit can be cancelled.');
        }
        DB::table('inspection_visits')->where('id', $id)->update(['status' => Inspection::CANCELLED,
            'cancel_reason' => trim($request->input('cancel_reason')), 'cancelled_by' => \Auth::id(), 'cancelled_at' => now(),
            'updated_by' => \Auth::id(), 'updated_at' => now()]);
        Inspection::syncAccess($visit->executive_id);
        return back()->with('success', $visit->visit_no . ' cancelled.');
    }

    private function ownScheduledVisit($id)
    {
        $visit = DB::table('inspection_visits as v')->join('buildings as b', 'b.id', '=', 'v.building_id')
            ->leftJoin('locations as l', 'l.id', '=', 'b.location_id')
            ->where('v.id', $id)->select('v.*', 'b.building_name', 'b.building_code', 'b.building_address', 'b.building_no_floor',
                'b.watchman_no', 'b.location_id', 'l.locations_name as location_name')->first();
        abort_unless($visit, 404);
        abort_unless((int) $visit->executive_id === (int) \Auth::id(), 403, 'Only the assigned executive can carry out this inspection.');
        return $visit;
    }

    /** Open issues of the building to be followed up during the visit. */
    private function pendingIssues($buildingId)
    {
        return DB::table('inspection_issues as i')->leftJoin('works as w', 'w.id', '=', 'i.work_id')
            ->leftJoin('units as u', 'u.id', '=', 'i.unit_id')->leftJoin('inspection_areas as a', 'a.id', '=', 'i.area_id')
            ->where('i.building_id', $buildingId)->whereIn('i.status', [Inspection::OPEN, Inspection::IN_PROGRESS])
            ->select('i.*', 'w.works_code as category', 'u.unit_no', 'a.name as area')
            ->orderBy('i.priority')->orderBy('i.raised_at')->get();
    }

    private function checklist()
    {
        $areas = DB::table('inspection_areas')->where('status', 1)->orderBy('sort_order')->orderBy('id')->get();
        $items = DB::table('inspection_checklist_items')->where('status', 1)->whereIn('area_id', $areas->pluck('id')->all() ?: [0])
            ->orderBy('sort_order')->orderBy('id')->get()->groupBy('area_id');
        return [$areas->filter(function ($a) use ($items) { return isset($items[$a->id]); })->values(), $items];
    }

    public function inspect($id)
    {
        $visit = $this->ownScheduledVisit($id);
        if ((int) $visit->status !== Inspection::SCHEDULED) {
            return redirect()->route('inspection.visits.show', $id);
        }
        if (!$visit->started_at) {
            DB::table('inspection_visits')->where('id', $id)->update(['started_at' => now()]);
        }
        $pending = $this->pendingIssues($visit->building_id);
        list($areas, $items) = $this->checklist();
        $categories = Inspection::categories();
        $units = DB::table('units')->where('building_id', $visit->building_id)->orderBy('unit_no')->pluck('unit_no', 'id')->all();
        $lastVisit = DB::table('inspection_visits')->where('building_id', $visit->building_id)->where('status', Inspection::COMPLETED)
            ->orderBy('completed_at', 'desc')->first();
        $executiveName = Inspection::userName($visit->executive_id);
        return view('maintenance::Inspection.inspect', compact('visit', 'pending', 'areas', 'items', 'categories', 'units', 'lastVisit', 'executiveName'));
    }

    public function submit(Request $request, $id)
    {
        $visit = $this->ownScheduledVisit($id);
        if ((int) $visit->status !== Inspection::SCHEDULED) {
            return redirect()->route('inspection.visits.show', $id)->with('error', 'This visit is already ' . strtolower(Inspection::VISIT_STATUSES[$visit->status]) . '.');
        }
        $pending = $this->pendingIssues($visit->building_id);
        list($areas, $items) = $this->checklist();
        $categories = Inspection::categories();
        $results = (array) $request->input('results', []);
        $issueIn = (array) $request->input('issue', []);
        $followIn = (array) $request->input('followup', []);

        $errors = [];
        foreach ($pending as $p) {
            $f = isset($followIn[$p->id]) ? $followIn[$p->id] : [];
            $cond = isset($f['condition']) ? (int) $f['condition'] : 0;
            if (!isset(Inspection::CONDITIONS[$cond]) || trim(isset($f['note']) ? $f['note'] : '') === '') {
                $errors[] = $p->issue_no . ': select the current condition and add an update note.';
            } elseif ($cond === 2 && !$request->hasFile('followup_photo.' . $p->id)) {
                $errors[] = $p->issue_no . ': attach an after photo to mark it resolved.';
            }
        }
        $missing = 0;
        foreach ($areas as $a) {
            foreach ($items[$a->id] as $it) {
                $r = isset($results[$it->id]) ? (int) $results[$it->id] : 0;
                if (!isset(Inspection::RESULTS[$r])) {
                    $missing++;
                    continue;
                }
                if ($r === 2) {
                    $d = isset($issueIn[$it->id]) ? $issueIn[$it->id] : [];
                    $label = $a->name . ' - ' . $it->name;
                    if (trim(isset($d['description']) ? $d['description'] : '') === '') {
                        $errors[] = "$label: add an issue description.";
                    }
                    if (!isset($d['work_id']) || !array_key_exists((int) $d['work_id'], $categories)) {
                        $errors[] = "$label: select a category.";
                    }
                    if (!isset($d['priority']) || !isset(Inspection::PRIORITIES[(int) $d['priority']])) {
                        $errors[] = "$label: select a priority.";
                    }
                    if (!$request->hasFile('issue_photo.' . $it->id)) {
                        $errors[] = "$label: attach a before photo.";
                    }
                }
            }
        }
        if ($missing) {
            array_unshift($errors, "Mark every checklist line: $missing still need OK, Issue or N/A.");
        }
        foreach ($request->allFiles() as $group) {
            foreach ((array) $group as $file) {
                if ($file && (!$file->isValid() || strpos((string) $file->getMimeType(), 'image/') !== 0)) {
                    $errors[] = 'Photos must be image files (JPG or PNG).';
                    break 2;
                }
            }
        }
        if ($errors) {
            return back()->withInput()->withErrors($errors);
        }

        $created = 0;
        DB::transaction(function () use ($request, $visit, $pending, $areas, $items, $results, $issueIn, $followIn, &$created) {
            foreach ($pending as $p) {
                $f = $followIn[$p->id];
                $cond = (int) $f['condition'];
                $updateId = Inspection::addUpdate($p->id, 'followup', trim($f['note']), $visit->id, $cond);
                $set = ['updated_by' => \Auth::id(), 'updated_at' => now()];
                if ($cond === 2) {
                    Inspection::storePhoto($request->file('followup_photo.' . $p->id), $p->id, 3, $updateId);
                    $set += ['status' => Inspection::RESOLVED, 'resolved_at' => now(), 'resolved_by' => \Auth::id()];
                } elseif ($cond === 3) {
                    $priority = max(1, (int) $p->priority - 1);
                    $target = Inspection::targetDate($priority);
                    $set += ['priority' => $priority, 'target_date' => min($target, $p->target_date)];
                    Inspection::recordChanges($p, ['priority' => $set['priority'], 'target_date' => $set['target_date']], $updateId);
                }
                if ($cond !== 2 && $request->hasFile('followup_photo.' . $p->id)) {
                    Inspection::storePhoto($request->file('followup_photo.' . $p->id), $p->id, 2, $updateId);
                }
                DB::table('inspection_issues')->where('id', $p->id)->update($set);
            }
            foreach ($areas as $a) {
                foreach ($items[$a->id] as $it) {
                    $r = (int) $results[$it->id];
                    $issueId = null;
                    if ($r === 2) {
                        $d = $issueIn[$it->id];
                        $issueId = Inspection::createIssue([
                            'building_id' => $visit->building_id, 'unit_id' => isset($d['unit_id']) ? $d['unit_id'] : null,
                            'area_id' => $a->id, 'checklist_item_id' => $it->id, 'work_id' => (int) $d['work_id'],
                            'priority' => (int) $d['priority'], 'source' => 1, 'visit_id' => $visit->id,
                            'executive_id' => $visit->executive_id, 'description' => $d['description'],
                        ], $request->file('issue_photo.' . $it->id));
                        $created++;
                    }
                    DB::table('inspection_visit_results')->insert(['visit_id' => $visit->id, 'area_id' => $a->id, 'checklist_item_id' => $it->id,
                        'result' => $r, 'issue_id' => $issueId, 'created_at' => now(), 'updated_at' => now()]);
                }
            }
            $lat = $request->input('latitude');
            $lng = $request->input('longitude');
            DB::table('inspection_visits')->where('id', $visit->id)->update([
                'status' => Inspection::COMPLETED, 'completed_at' => now(), 'general_remarks' => $request->input('general_remarks'),
                'latitude' => is_numeric($lat) ? $lat : null, 'longitude' => is_numeric($lng) ? $lng : null,
                'updated_by' => \Auth::id(), 'updated_at' => now(),
            ]);
        });
        Inspection::syncAccess($visit->executive_id);
        return redirect()->route('inspection.visits.show', $visit->id)
            ->with('success', 'Inspection submitted. ' . $created . ' new issue' . ($created === 1 ? '' : 's') . ' recorded, '
                . count($pending) . ' pending issue' . (count($pending) === 1 ? '' : 's') . ' updated.');
    }

    public function show($id)
    {
        $q = DB::table('inspection_visits as v')->join('buildings as b', 'b.id', '=', 'v.building_id')->where('v.id', $id)
            ->select('v.*', 'b.building_name', 'b.building_code');
        $visit = Inspection::scope($q, 'v')->first();
        abort_unless($visit, 404);
        $results = DB::table('inspection_visit_results as r')->join('inspection_areas as a', 'a.id', '=', 'r.area_id')
            ->join('inspection_checklist_items as c', 'c.id', '=', 'r.checklist_item_id')
            ->leftJoin('inspection_issues as i', 'i.id', '=', 'r.issue_id')
            ->where('r.visit_id', $id)->orderBy('a.sort_order')->orderBy('c.sort_order')
            ->select('r.*', 'a.name as area', 'c.name as item', 'i.issue_no', 'i.description', 'i.priority')->get()->groupBy('area');
        $followups = DB::table('inspection_issue_updates as u')->join('inspection_issues as i', 'i.id', '=', 'u.issue_id')
            ->where('u.visit_id', $id)->where('u.kind', 'followup')->select('u.*', 'i.issue_no', 'i.description', 'i.id as issue_id')->get();
        $names = Inspection::userNames([$visit->executive_id, $visit->created_by, $visit->cancelled_by]);
        return view('maintenance::Inspection.visit_show', compact('visit', 'results', 'followups', 'names'));
    }
}
