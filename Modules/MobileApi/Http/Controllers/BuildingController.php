<?php

namespace Modules\MobileApi\Http\Controllers;

use Illuminate\Http\Request;
use Modules\BackOffice\Services\TenantOnboarding;
use Modules\BackOffice\Services\TerminationInspection;
use Modules\Maintenance\Services\TicketAttendance;
use Modules\Masters\Entities\Building;
use Modules\Masters\Services\AreBuildings;
use Modules\MobileApi\Http\Resources\BuildingResource;

class BuildingController extends ApiController
{
    /** My buildings with the open-item counts the home screen shows. */
    public function index(Request $request)
    {
        $user = $request->user();
        $ids = AreBuildings::forUser($user);
        $buildings = Building::whereIn('id', $ids)->withCount('unit')->orderBy('building_name')->get();

        return response()->json(['data' => $buildings->map(fn ($b) => (new BuildingResource($b))->toArray($request) + [
            'units' => (int) $b->unit_count,
            'unassigned_complaints' => TicketAttendance::unassignedTickets($user, $b->id)->count(),
            'my_complaints' => TicketAttendance::myTickets($user, 'mine', $b->id)->count(),
            'pending_inspections' => TerminationInspection::queue($user, 'pending', $b->id)->count(),
            'open_onboardings' => TenantOnboarding::queue($user, 'open', $b->id)->count(),
        ])->values()]);
    }
}
