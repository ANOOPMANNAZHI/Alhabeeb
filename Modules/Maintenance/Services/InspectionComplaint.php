<?php

namespace Modules\Maintenance\Services;

use Illuminate\Support\Facades\DB;
use Modules\General\Http\Controllers\GeneralController as General;
use Modules\Maintenance\Entities\ComplaintChecklist;
use Modules\Maintenance\Entities\ComplaintEnquiry;
use Modules\Maintenance\Entities\ComplaintProcess;
use Modules\Maintenance\Events\NewComplaintEnquiry;

/**
 * Raise a complaint ticket from an inspection issue, the same way MaintenanceController@store does
 * (complaint + one ticket for the issue's Work + process 701 assignment + notification).
 * Category: 2 when a unit is given (unit only, no tenant), 3 = Other (common area). The tenant is
 * never attached, so no tenant email / SMS goes out. PHP < 7.4.
 */
class InspectionComplaint
{
    const PROCESS = 701;

    /** @return ComplaintEnquiry */
    public static function create($issue, $complainerName, $mobile, $highPriority)
    {
        $building = DB::table('buildings')->where('id', $issue->building_id)->first();
        $tenantStatus = (int) $building->building_maintenance_info === 1 ? 1 : 0; // maintained by landlord
        $general = new General;
        $users = [];

        $prefix = prefixData('complaint_prefix')->configuration_value;
        $latest = ComplaintEnquiry::orderBy('id', 'desc')->first();
        $complaint = ComplaintEnquiry::create([
            'complaint_date'      => date('Y-m-d'),
            'complaint_no'        => $prefix . str_pad(($latest ? $latest->id : 0) + 1, 4, '0', STR_PAD_LEFT),
            'building_id'         => $issue->building_id,
            'location_id'         => $building->location_id,
            'complainer_name'     => $complainerName,
            'complaint_mob_no'    => $mobile,
            'way_no'              => $building->building_pc,
            'unit_id'             => $issue->unit_id ?: null,
            'priority_status'     => $highPriority ? 1 : 0,
            'complainer_category' => $issue->unit_id ? 2 : 3,
            'tenant_status'       => $tenantStatus,
            'created_by'          => \Auth::id(),
        ]);
        $processAssign = $general->roleUsersFromProcess(self::PROCESS, null, null, $tenantStatus);

        $checklist = ComplaintChecklist::create([
            'complaint_enquiries_id'   => $complaint->id,
            'work_id'                  => $issue->work_id,
            'checklist_desc'           => $issue->description . ' (Inspection issue ' . $issue->issue_no . ')',
            'complaint_ticket_no'      => $complaint->complaint_no . '-SUB1',
            'work_flow_processes_code' => self::PROCESS,
            'created_by'               => \Auth::id(),
        ]);
        $process = ComplaintProcess::create([
            'complaint_checklists_id'  => $checklist->id,
            'complaint_enquiries_id'   => $complaint->id,
            'work_flow_processes_code' => self::PROCESS,
            'created_by'               => \Auth::id(),
        ]);
        if ($processAssign == false) {
            $res = $general->workFlowProcess(self::PROCESS);
            $process->complaintUsers()->attach($res->default_role, ['user_id' => $res->default_user_id]);
            $users = empty($res->default_user_id) ? \App\User::role($res->default_role)->pluck('id')->all() : [$res->default_user_id];
        } else {
            foreach ($processAssign->assign as $a) {
                $userId = $a->user_id ?: null;
                $process->complaintUsers()->attach($a->role_id, ['user_id' => $userId]);
                $users = array_merge($users, $userId ? [$userId] : \App\User::role($a->role_id)->pluck('id')->all());
            }
        }
        $users = array_merge($users, \App\User::role(['facility_manager'])->pluck('id')->all());
        $complaint->href = url('complaintStage/' . $complaint->id . '/UnAssignedList');
        event(new NewComplaintEnquiry($complaint, \App\User::whereIn('id', array_unique($users))->get()));
        return $complaint;
    }
}
