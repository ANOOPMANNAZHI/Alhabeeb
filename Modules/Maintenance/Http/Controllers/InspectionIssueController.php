<?php

namespace Modules\Maintenance\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Maintenance\Services\Inspection;
use Modules\Maintenance\Services\InspectionComplaint;

/**
 * Inspection issue register: list, manual issue, detail with action plan, progress timeline,
 * resolve (after photo), verify & close / send back, optional complaint ticket.
 * PHP < 7.4: no arrow functions.
 */
class InspectionIssueController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:inspection_view|inspection_issue_create|inspection_verify');
        $this->middleware('permission:inspection_issue_create')->only(['store']);
        $this->middleware('permission:inspection_complaint_create')->only(['createComplaint']);
    }

    private function baseQuery()
    {
        $q = DB::table('inspection_issues as i')->join('buildings as b', 'b.id', '=', 'i.building_id')
            ->leftJoin('works as w', 'w.id', '=', 'i.work_id')->leftJoin('units as u', 'u.id', '=', 'i.unit_id')
            ->leftJoin('inspection_areas as a', 'a.id', '=', 'i.area_id')
            ->leftJoin('complaint_enquiries as ce', 'ce.id', '=', 'i.complaint_enquiry_id')
            ->select('i.*', 'b.building_name', 'w.works_code as category', 'u.unit_no', 'a.name as area', 'ce.complaint_no');
        return Inspection::scope($q, 'i');
    }

    public function index(Request $request)
    {
        $f = [];
        foreach (['q', 'executive', 'building', 'priority', 'status', 'category', 'overdue', 'source'] as $k) {
            $f[$k] = $request->input($k);
        }
        if (!$request->has('status')) {
            $f['status'] = 'active';
        }
        $q = $this->baseQuery();
        if ($f['q']) {
            $like = '%' . trim($f['q']) . '%';
            $q->where(function ($w) use ($like) {
                $w->where('i.issue_no', 'ilike', $like)->orWhere('i.description', 'ilike', $like)->orWhere('b.building_name', 'ilike', $like)
                    ->orWhere('u.unit_no', 'ilike', $like)->orWhere('ce.complaint_no', 'ilike', $like);
            });
        }
        foreach (['executive' => 'i.executive_id', 'building' => 'i.building_id', 'priority' => 'i.priority', 'category' => 'i.work_id', 'source' => 'i.source'] as $k => $col) {
            if ($f[$k]) {
                $q->where($col, $f[$k]);
            }
        }
        if ($f['status'] === 'active') {
            $q->where('i.status', '<>', Inspection::CLOSED);
        } elseif ($f['status']) {
            $q->where('i.status', $f['status']);
        }
        if ($f['overdue']) {
            $q->where('i.status', '<', Inspection::RESOLVED)->where('i.target_date', '<', date('Y-m-d'));
        }
        // newest first (user, 18/10/2026)
        $issues = $q->orderBy('i.created_at', 'desc')->orderBy('i.id', 'desc')->paginate(50)->appends($request->query());
        $names = Inspection::userNames(array_merge($issues->pluck('executive_id')->all(), $issues->pluck('raised_by')->all()));

        $counts = Inspection::scope(DB::table('inspection_issues as i'), 'i')
            ->selectRaw("count(*) filter (where status <> 4) active, count(*) filter (where status < 3 and target_date < current_date) overdue,
                count(*) filter (where status <> 4 and priority = 1) critical, count(*) filter (where status = 3) awaiting")->first();
        $buildings = Inspection::buildings();
        $executives = Inspection::executives();
        $categories = Inspection::categories();
        $areas = DB::table('inspection_areas')->where('status', 1)->orderBy('sort_order')->pluck('name', 'id')->all();
        $buildingExec = DB::table('building_executives')->whereNull('assigned_till')->pluck('user_id', 'building_id')->all();
        $seesAll = Inspection::seesAll();
        return view('maintenance::Inspection.issues', compact('issues', 'names', 'f', 'counts', 'buildings', 'executives', 'categories',
            'areas', 'buildingExec', 'seesAll'));
    }

    /** Units of a building for the issue forms. */
    public function units($buildingId)
    {
        abort_unless(array_key_exists((int) $buildingId, Inspection::buildings()), 403);
        return response()->json(DB::table('units')->where('building_id', $buildingId)->orderBy('unit_no')->get(['id', 'unit_no']));
    }

    public function store(Request $request)
    {
        $request->validate([
            'building_id'  => 'required|integer',
            'area_id'      => 'nullable|integer|exists:inspection_areas,id',
            'work_id'      => 'required|integer',
            'priority'     => 'required|in:1,2,3,4',
            'description'  => 'required|string|max:2000',
            'unit_id'      => 'nullable|integer',
            'executive_id' => 'nullable|integer',
            'photo'        => 'required|image|max:15360',
        ], ['photo.required' => 'Attach a before photo.', 'photo.image' => 'The before photo must be an image (JPG or PNG).',
            'building_id.required' => 'Select a building.', 'work_id.required' => 'Select a category.']);
        $buildingId = (int) $request->input('building_id');
        if (!array_key_exists($buildingId, Inspection::buildings())) {
            return back()->withInput()->withErrors(['building_id' => 'Select one of your buildings.']);
        }
        if (!array_key_exists((int) $request->input('work_id'), Inspection::categories())) {
            return back()->withInput()->withErrors(['work_id' => 'Select a category.']);
        }
        $unitId = $request->input('unit_id') ?: null;
        if ($unitId && !DB::table('units')->where('id', $unitId)->where('building_id', $buildingId)->exists()) {
            return back()->withInput()->withErrors(['unit_id' => 'The unit is not in this building.']);
        }
        $executiveId = Inspection::seesAll() ? (int) $request->input('executive_id') : (int) Inspection::buildingExecutive($buildingId);
        if (!$executiveId && !Inspection::seesAll()) {
            $executiveId = \Auth::id();
        }
        if (!array_key_exists($executiveId, Inspection::executives()) && $executiveId !== \Auth::id()) {
            return back()->withInput()->withErrors(['executive_id' => 'Select the executive responsible for this issue.']);
        }
        $id = DB::transaction(function () use ($request, $buildingId, $unitId, $executiveId) {
            return Inspection::createIssue([
                'building_id' => $buildingId, 'unit_id' => $unitId, 'area_id' => $request->input('area_id'),
                'work_id' => (int) $request->input('work_id'), 'priority' => (int) $request->input('priority'), 'source' => 2,
                'executive_id' => $executiveId, 'description' => $request->input('description'),
            ], $request->file('photo'));
        });
        $issue = DB::table('inspection_issues')->where('id', $id)->first();
        Inspection::syncAccess($executiveId);
        return redirect()->route('inspection.issues.show', $id)
            ->with('success', $issue->issue_no . ' created. Target date ' . date('d/m/Y', strtotime($issue->target_date)) . '.');
    }

    private function find($id)
    {
        $issue = $this->baseQuery()->where('i.id', $id)->first();
        abort_unless($issue, 404);
        return $issue;
    }

    public function show($id)
    {
        $issue = $this->find($id);
        $updates = DB::table('inspection_issue_updates')->where('issue_id', $id)->orderBy('created_at')->orderBy('id')->get();
        $photos = DB::table('inspection_photos')->where('issue_id', $id)->orderBy('id')->get();
        $updatePhotos = $photos->groupBy('update_id');
        $changes = DB::table('inspection_issue_changes')->where('issue_id', $id)->orderBy('changed_at', 'desc')->orderBy('id', 'desc')->get();
        $names = Inspection::userNames(array_merge([$issue->executive_id, $issue->raised_by, $issue->resolved_by, $issue->closed_by],
            $updates->pluck('created_by')->all(), $changes->pluck('changed_by')->all()));
        // one row per save: when, who, reason, and the fields it changed
        $changeSets = $changes->groupBy(function ($c) { return $c->update_id ?: 'c' . $c->id; })->values();
        $lastPlan = $updates->where('kind', 'plan')->last();
        $visit = $issue->visit_id ? DB::table('inspection_visits')->where('id', $issue->visit_id)->first() : null;
        $canWork = Inspection::canWork($issue);
        $canVerify = Inspection::canVerify($issue);
        $canComplaint = $canWork && !$issue->complaint_enquiry_id && \Auth::user()->can('inspection_complaint_create');
        $me = DB::table('users as u')->leftJoin('employees as e', 'e.id', '=', 'u.user_type_id')->where('u.id', \Auth::id())
            ->select(DB::raw('coalesce(e.employee_name, u.username) as name'), 'e.employee_contact_no')->first();
        return view('maintenance::Inspection.issue_show', compact('issue', 'updates', 'photos', 'updatePhotos', 'names', 'visit',
            'canWork', 'canVerify', 'canComplaint', 'me', 'changeSets', 'lastPlan'));
    }

    private function workable($id)
    {
        $issue = $this->find($id);
        abort_unless(Inspection::canWork($issue), 403, 'Only the assigned executive or a manager can update this issue.');
        return $issue;
    }

    public function savePlan(Request $request, $id)
    {
        $issue = $this->workable($id);
        $request->validate([
            'action_plan'       => 'required|string|max:2000',
            'responsible_party' => 'required|in:' . implode(',', Inspection::PARTIES),
            'estimated_cost'    => 'required|numeric|min:0|max:999999999',
            'landlord_approval' => 'required|in:0,1',
            'target_date'       => 'required|date_format:Y-m-d',
            'change_reason'     => 'nullable|string|max:1000',
        ], ['action_plan.required' => 'Describe the action to be taken.']);
        $values = [
            'action_plan'       => trim($request->input('action_plan')),
            'responsible_party' => $request->input('responsible_party'),
            'estimated_cost'    => round((float) $request->input('estimated_cost'), 3),
            'landlord_approval' => (bool) $request->input('landlord_approval'),
            'target_date'       => $request->input('target_date'),
        ];
        $first = $issue->action_plan === null || $issue->action_plan === '';
        if (!$first && !Inspection::wouldChange($issue, $values)) {
            return redirect()->route('inspection.issues.show', $id)->with('success', 'Nothing changed - the action plan is the same as before.');
        }
        $changed = [];
        DB::transaction(function () use ($request, $issue, $values, $first, &$changed) {
            $reason = trim((string) $request->input('change_reason'));
            $note = $first ? 'Action plan added.' : 'Action plan changed: ' . implode(', ', Inspection::wouldChange($issue, $values)) . '.';
            if (!$first && $reason !== '') {
                $note .= "\nReason: " . $reason;
            }
            $updateId = Inspection::addUpdate($issue->id, 'plan', $note);
            $changed = Inspection::recordChanges($issue, $values, $updateId);
            DB::table('inspection_issues')->where('id', $issue->id)->update($values + [
                'status' => (int) $issue->status === Inspection::OPEN ? Inspection::IN_PROGRESS : $issue->status,
                'updated_by' => \Auth::id(), 'updated_at' => now(),
            ]);
        });
        return redirect()->route('inspection.issues.show', $id)
            ->with('success', $first ? 'Action plan saved.' : 'Action plan updated (' . implode(', ', $changed) . ').');
    }

    public function addProgress(Request $request, $id)
    {
        $issue = $this->workable($id);
        $request->validate(['note' => 'required|string|max:2000', 'photo' => 'nullable|image|max:15360'],
            ['note.required' => 'Write what has changed.', 'photo.image' => 'The photo must be an image (JPG or PNG).']);
        DB::transaction(function () use ($request, $issue) {
            $updateId = Inspection::addUpdate($issue->id, 'progress', trim($request->input('note')));
            if ($request->hasFile('photo')) {
                Inspection::storePhoto($request->file('photo'), $issue->id, 2, $updateId);
            }
            if ((int) $issue->status === Inspection::OPEN) {
                DB::table('inspection_issues')->where('id', $issue->id)->update(['status' => Inspection::IN_PROGRESS, 'updated_by' => \Auth::id(), 'updated_at' => now()]);
            }
        });
        return redirect()->route('inspection.issues.show', $id)->with('success', 'Progress update added.');
    }

    public function resolve(Request $request, $id)
    {
        $issue = $this->workable($id);
        if ((int) $issue->status === Inspection::RESOLVED) {
            return back()->with('error', 'This issue is already resolved and waiting for verification.');
        }
        $request->validate(['photo' => 'required|image|max:15360', 'note' => 'nullable|string|max:2000'],
            ['photo.required' => 'Attach an after photo to mark the issue resolved.', 'photo.image' => 'The after photo must be an image (JPG or PNG).']);
        DB::transaction(function () use ($request, $issue) {
            $note = trim((string) $request->input('note'));
            $updateId = Inspection::addUpdate($issue->id, 'resolved', 'Marked resolved' . ($note !== '' ? ': ' . $note : '.'));
            Inspection::storePhoto($request->file('photo'), $issue->id, 3, $updateId);
            DB::table('inspection_issues')->where('id', $issue->id)->update(['status' => Inspection::RESOLVED, 'resolved_at' => now(),
                'resolved_by' => \Auth::id(), 'updated_by' => \Auth::id(), 'updated_at' => now()]);
        });
        return redirect()->route('inspection.issues.show', $id)->with('success', 'Issue resolved - waiting for supervisor verification.');
    }

    public function verify(Request $request, $id)
    {
        $issue = $this->find($id);
        abort_unless(Inspection::canVerify($issue), 403);
        $request->validate(['decision' => 'required|in:close,send_back', 'note' => 'nullable|string|max:2000']);
        $note = trim((string) $request->input('note'));
        if ($request->input('decision') === 'send_back' && $note === '') {
            return back()->withErrors(['note' => 'Write why the issue is sent back.']);
        }
        DB::transaction(function () use ($request, $issue, $note) {
            if ($request->input('decision') === 'close') {
                Inspection::addUpdate($issue->id, 'verified', 'Rectification verified and issue closed' . ($note !== '' ? ': ' . $note : '.'));
                DB::table('inspection_issues')->where('id', $issue->id)->update(['status' => Inspection::CLOSED, 'closed_at' => now(),
                    'closed_by' => \Auth::id(), 'updated_by' => \Auth::id(), 'updated_at' => now()]);
            } else {
                Inspection::addUpdate($issue->id, 'sent_back', 'Sent back: ' . $note);
                DB::table('inspection_issues')->where('id', $issue->id)->update(['status' => Inspection::IN_PROGRESS, 'resolved_at' => null,
                    'resolved_by' => null, 'updated_by' => \Auth::id(), 'updated_at' => now()]);
            }
        });
        Inspection::syncAccess($issue->executive_id);
        return redirect()->route('inspection.issues.show', $id)
            ->with('success', $request->input('decision') === 'close' ? 'Issue verified and closed.' : 'Issue sent back to the executive.');
    }

    public function createComplaint(Request $request, $id)
    {
        $issue = $this->workable($id);
        if ($issue->complaint_enquiry_id) {
            return back()->with('error', 'A complaint ticket already exists for this issue.');
        }
        $request->validate(['complainer_name' => 'required|string|max:100', 'complaint_mob_no' => 'required|string|max:20'],
            ['complainer_name.required' => 'Enter the contact name.', 'complaint_mob_no.required' => 'Enter the contact mobile number.']);
        $complaint = DB::transaction(function () use ($request, $issue) {
            $c = InspectionComplaint::create($issue, trim($request->input('complainer_name')), trim($request->input('complaint_mob_no')),
                (int) $issue->priority <= 2);
            DB::table('inspection_issues')->where('id', $issue->id)->update(['complaint_enquiry_id' => $c->id, 'updated_by' => \Auth::id(), 'updated_at' => now()]);
            Inspection::addUpdate($issue->id, 'complaint', 'Complaint ticket ' . $c->complaint_no . '-SUB1 created for the maintenance team.');
            return $c;
        });
        return redirect()->route('inspection.issues.show', $id)->with('success', 'Complaint ' . $complaint->complaint_no . ' created.');
    }
}
