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
        $tab = $request->get('tab') === 'checklist' ? 'checklist' : 'buildings';
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

    /** Assign (or unassign with user_id empty) the ticked buildings. */
    public function assign(Request $request)
    {
        $request->validate([
            'building_ids'   => 'required|array|min:1',
            'building_ids.*' => 'integer',
            'user_id'        => 'required',
        ], ['building_ids.required' => 'Tick at least one building.', 'user_id.required' => 'Select the executive, or Nobody to unassign.']);
        $userId = $request->input('user_id') === 'none' ? null : (int) $request->input('user_id');
        if ($userId && !array_key_exists($userId, Inspection::executives())) {
            return back()->withErrors(['user_id' => 'Select an active employee.']);
        }
        $today = date('Y-m-d');
        $n = 0;
        $touched = [$userId];
        DB::transaction(function () use ($request, $userId, $today, &$n, &$touched) {
            foreach ($request->input('building_ids') as $buildingId) {
                $current = DB::table('building_executives')->where('building_id', $buildingId)->whereNull('assigned_till')->lockForUpdate()->first();
                if ($current && (int) $current->user_id === (int) $userId) {
                    continue;
                }
                if ($current) {
                    $touched[] = $current->user_id;
                    DB::table('building_executives')->where('id', $current->id)
                        ->update(['assigned_till' => $today, 'updated_by' => \Auth::id(), 'updated_at' => now()]);
                }
                if ($userId) {
                    DB::table('building_executives')->insert(['building_id' => $buildingId, 'user_id' => $userId, 'assigned_from' => $today,
                        'created_by' => \Auth::id(), 'created_at' => now(), 'updated_at' => now()]);
                }
                $n++;
            }
        });
        Inspection::syncAccess($touched); // the new person gets the screens; a previous one with nothing left loses them
        $msg = $userId ? "$n building(s) assigned to " . Inspection::userName($userId) . '.' : "$n building(s) unassigned.";
        return redirect()->route('inspection.setup')->with('success', $msg);
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
