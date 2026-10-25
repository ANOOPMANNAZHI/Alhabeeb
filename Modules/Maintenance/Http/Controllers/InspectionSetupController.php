<?php

namespace Modules\Maintenance\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Maintenance\Services\Inspection;

/**
 * Building Inspection setup: building -> executive assignment, areas and checklist lines.
 * PHP < 7.4: no arrow functions.
 */
class InspectionSetupController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:inspection_masters');
    }

    public function index(Request $request)
    {
        $tab = $request->input('tab') === 'checklist' ? 'checklist' : 'buildings';
        $executives = Inspection::executives();
        $buildings = DB::table('buildings as b')
            ->leftJoin('building_executives as be', function ($j) { $j->on('be.building_id', '=', 'b.id')->whereNull('be.assigned_till'); })
            ->where('b.building_status', 1)
            ->select('b.id', 'b.building_name', 'b.building_code', 'be.user_id', 'be.assigned_from')
            ->orderBy('b.building_name')->get();
        $names = Inspection::userNames($buildings->pluck('user_id')->all());
        $areas = DB::table('inspection_areas')->orderBy('sort_order')->orderBy('id')->get();
        $items = DB::table('inspection_checklist_items')->orderBy('sort_order')->orderBy('id')->get()->groupBy('area_id');
        $counts = [];
        foreach ($buildings as $b) {
            if ($b->user_id) {
                $counts[$b->user_id] = (isset($counts[$b->user_id]) ? $counts[$b->user_id] : 0) + 1;
            }
        }
        return view('maintenance::Inspection.setup', compact('tab', 'executives', 'buildings', 'names', 'areas', 'items', 'counts'));
    }

    /**
     * Assign (or unassign with user_id "none") the ticked buildings from an effective date (today or
     * earlier): the previous person handled the building till the day before (user, 07/10/2026).
     * Their scheduled visits and open issues of the building move to the new person.
     */
    public function assign(Request $request)
    {
        $request->validate([
            'building_ids'   => 'required|array|min:1',
            'building_ids.*' => 'integer',
            'user_id'        => 'required',
            'effective_from' => 'required|date|before_or_equal:today',
        ], ['building_ids.required' => 'Tick at least one building.', 'user_id.required' => 'Select employee, or Nobody to unassign.',
            'effective_from.required' => 'Enter the date the change takes effect.',
            'effective_from.before_or_equal' => 'The effective date cannot be in the future.']);
        $userId = $request->input('user_id') === 'none' ? null : (int) $request->input('user_id');
        if ($userId && !array_key_exists($userId, Inspection::executives())) {
            return back()->withErrors(['user_id' => 'Select an active employee.']);
        }
        $from = date('Y-m-d', strtotime($request->input('effective_from')));
        $n = 0;
        $moved = 0;
        $touched = [$userId];
        $errors = [];
        DB::transaction(function () use ($request, $userId, $from, &$n, &$moved, &$touched, &$errors) {
            foreach ($request->input('building_ids') as $buildingId) {
                $current = DB::table('building_executives')->where('building_id', $buildingId)->whereNull('assigned_till')->lockForUpdate()->first();
                if ($current && (int) $current->user_id === (int) $userId) {
                    continue;
                }
                $name = DB::table('buildings')->where('id', $buildingId)->value('building_name');
                if ($current && $from < $current->assigned_from) {
                    $errors[] = $name . ': ' . Inspection::userName($current->user_id) . ' only started on '
                        . date('d/m/Y', strtotime($current->assigned_from)) . ', so the change cannot be earlier.';
                    continue;
                }
                $lastTill = DB::table('building_executives')->where('building_id', $buildingId)->whereNotNull('assigned_till')->max('assigned_till');
                if (!$current && $userId && $lastTill && $from < $lastTill) {
                    $errors[] = $name . ': the last executive handled it till ' . date('d/m/Y', strtotime($lastTill)) . ', so start on or after that date.';
                    continue;
                }
                if ($current) {
                    $touched[] = $current->user_id;
                    // till the day before; changed on the day they started -> kept in History for that one day
                    $till = $from === $current->assigned_from ? $from : date('Y-m-d', strtotime($from . ' -1 day'));
                    DB::table('building_executives')->where('id', $current->id)->update([
                        'assigned_till' => $till, 'updated_by' => \Auth::id(), 'updated_at' => now()]);
                    if ($userId) {
                        $moved += $this->moveOpenWork($buildingId, (int) $current->user_id, $userId, $from);
                    }
                }
                if ($userId) {
                    DB::table('building_executives')->insert(['building_id' => $buildingId, 'user_id' => $userId, 'assigned_from' => $from,
                        'created_by' => \Auth::id(), 'created_at' => now(), 'updated_at' => now()]);
                }
                $n++;
            }
        });
        Inspection::syncAccess($touched); // the new person gets the screens; a previous one with nothing left loses them
        $msg = $userId ? "$n building(s) assigned to " . Inspection::userName($userId) . ' from ' . date('d/m/Y', strtotime($from)) . '.'
                       : "$n building(s) unassigned from " . date('d/m/Y', strtotime($from)) . '.';
        if ($moved) {
            $msg .= " $moved open visit(s) / issue(s) moved to the new executive.";
        }
        $redirect = redirect()->route('inspection.setup')->with('success', $msg);
        return $errors ? $redirect->withErrors(['assign' => $errors]) : $redirect;
    }

    /** The previous executive's scheduled visits and open issues of the building go to the new one. Returns how many moved. */
    private function moveOpenWork($buildingId, $fromUser, $toUser, $date)
    {
        $visits = DB::table('inspection_visits')->where('building_id', $buildingId)->where('executive_id', $fromUser)
            ->where('status', Inspection::SCHEDULED)
            ->update(['executive_id' => $toUser, 'updated_by' => \Auth::id(), 'updated_at' => now()]);
        $issues = DB::table('inspection_issues')->where('building_id', $buildingId)->where('executive_id', $fromUser)
            ->where('status', '<>', Inspection::CLOSED)->pluck('id');
        $note = 'Reassigned from ' . Inspection::userName($fromUser) . ' to ' . Inspection::userName($toUser) . ' on ' . date('d/m/Y', strtotime($date)) . '.';
        foreach ($issues as $issueId) {
            DB::table('inspection_issues')->where('id', $issueId)->update(['executive_id' => $toUser, 'updated_by' => \Auth::id(), 'updated_at' => now()]);
            Inspection::addUpdate($issueId, 'reassigned', $note);
        }
        return $visits + count($issues);
    }

    /** History panel of one building: who handled it from / till (HTML for the modal). */
    public function history($buildingId)
    {
        $building = DB::table('buildings')->where('id', $buildingId)->first();
        abort_unless($building, 404);
        $rows = DB::table('building_executives')->where('building_id', $buildingId)
            ->orderByRaw('assigned_till IS NOT NULL')->orderBy('assigned_from', 'desc')->orderBy('id', 'desc')->get();
        $names = Inspection::userNames($rows->pluck('user_id')->merge($rows->pluck('created_by'))->merge($rows->pluck('updated_by'))->all());
        return view('maintenance::Inspection._assignment_history', compact('building', 'rows', 'names'));
    }

    /**
     * Correct the dates of one assignment. The neighbour that joins it (previous till / next from)
     * moves with it, so the periods stay continuous; they may never overlap.
     */
    public function updateHistory(Request $request, $id)
    {
        $row = DB::table('building_executives')->where('id', $id)->first();
        abort_unless($row, 404);
        $request->validate([
            'assigned_from' => 'required|date|before_or_equal:today',
            'assigned_till' => 'nullable|date|before_or_equal:today|after_or_equal:assigned_from',
        ], ['assigned_till.after_or_equal' => 'Till cannot be before From.', 'assigned_from.before_or_equal' => 'From cannot be in the future.',
            'assigned_till.before_or_equal' => 'Till cannot be in the future.']);
        $from = date('Y-m-d', strtotime($request->input('assigned_from')));
        $till = $row->assigned_till === null ? null : date('Y-m-d', strtotime($request->input('assigned_till') ?: $row->assigned_till));
        $back = redirect()->route('inspection.setup', ['history' => $row->building_id]);

        $all = DB::table('building_executives')->where('building_id', $row->building_id)->where('id', '<>', $row->id)
            ->orderBy('assigned_from')->get();
        $prev = $all->filter(function ($r) use ($row) { return $r->assigned_from < $row->assigned_from; })->last();
        $next = $all->first(function ($r) use ($row) { return $r->assigned_from > $row->assigned_from; });
        $day = function ($d, $n) { return date('Y-m-d', strtotime($d . ' ' . ($n >= 0 ? '+' : '') . $n . ' day')); };
        $joins = function ($till, $nextFrom) use ($day) { return $till !== null && ($nextFrom === $day($till, 1) || $nextFrom === $till); };

        $prevTill = null;
        if ($prev) {
            if ($joins($prev->assigned_till, $row->assigned_from)) {
                $prevTill = $day($from, -1);
                if ($prevTill < $prev->assigned_from) {
                    return $back->withErrors(['history' => 'From cannot be on or before ' . date('d/m/Y', strtotime($prev->assigned_from))
                        . ', when ' . Inspection::userName($prev->user_id) . ' started.']);
                }
            } elseif ($prev->assigned_till >= $from) {
                return $back->withErrors(['history' => 'From overlaps ' . Inspection::userName($prev->user_id) . ', who handled it till '
                    . date('d/m/Y', strtotime($prev->assigned_till)) . '.']);
            }
        }
        $nextFrom = null;
        if ($next && $till !== null) {
            if ($joins($row->assigned_till, $next->assigned_from)) {
                $nextFrom = $day($till, 1);
                if ($next->assigned_till !== null && $nextFrom > $next->assigned_till) {
                    return $back->withErrors(['history' => 'Till cannot be on or after ' . date('d/m/Y', strtotime($next->assigned_till))
                        . ', when ' . Inspection::userName($next->user_id) . ' finished.']);
                }
                if ($next->assigned_till === null && $nextFrom > date('Y-m-d')) {
                    return $back->withErrors(['history' => 'Till cannot be today: ' . Inspection::userName($next->user_id) . ' would then start in the future.']);
                }
            } elseif ($next->assigned_from <= $till) {
                return $back->withErrors(['history' => 'Till overlaps ' . Inspection::userName($next->user_id) . ', who started on '
                    . date('d/m/Y', strtotime($next->assigned_from)) . '.']);
            }
        }

        DB::transaction(function () use ($row, $from, $till, $prev, $prevTill, $next, $nextFrom) {
            $stamp = ['updated_by' => \Auth::id(), 'updated_at' => now()];
            DB::table('building_executives')->where('id', $row->id)->update(['assigned_from' => $from, 'assigned_till' => $till] + $stamp);
            if ($prevTill !== null) {
                DB::table('building_executives')->where('id', $prev->id)->update(['assigned_till' => $prevTill] + $stamp);
            }
            if ($nextFrom !== null) {
                DB::table('building_executives')->where('id', $next->id)->update(['assigned_from' => $nextFrom] + $stamp);
            }
        });
        return $back->with('success', 'Dates corrected for ' . Inspection::userName($row->user_id) . '.');
    }

    public function saveArea(Request $request)
    {
        $request->validate(['name' => 'required|string|max:100', 'id' => 'nullable|integer']);
        $data = ['name' => trim($request->input('name')), 'status' => $request->input('status', 1) ? 1 : 0, 'updated_by' => \Auth::id(), 'updated_at' => now()];
        if ($request->input('id')) {
            DB::table('inspection_areas')->where('id', $request->input('id'))->update($data);
        } else {
            DB::table('inspection_areas')->insert($data + ['sort_order' => (int) DB::table('inspection_areas')->max('sort_order') + 1,
                'created_by' => \Auth::id(), 'created_at' => now()]);
        }
        return redirect()->route('inspection.setup', ['tab' => 'checklist'])->with('success', 'Area saved.');
    }

    public function saveItem(Request $request)
    {
        $request->validate(['name' => 'required|string|max:200', 'area_id' => 'required|integer|exists:inspection_areas,id', 'id' => 'nullable|integer']);
        $data = ['name' => trim($request->input('name')), 'area_id' => $request->input('area_id'),
            'status' => $request->input('status', 1) ? 1 : 0, 'updated_by' => \Auth::id(), 'updated_at' => now()];
        if ($request->input('id')) {
            DB::table('inspection_checklist_items')->where('id', $request->input('id'))->update($data);
        } else {
            DB::table('inspection_checklist_items')->insert($data + [
                'sort_order' => (int) DB::table('inspection_checklist_items')->where('area_id', $data['area_id'])->max('sort_order') + 1,
                'created_by' => \Auth::id(), 'created_at' => now()]);
        }
        return redirect()->route('inspection.setup', ['tab' => 'checklist'])->with('success', 'Checklist line saved.');
    }
}
