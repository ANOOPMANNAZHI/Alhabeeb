<?php

namespace Modules\BackOffice\Services;

use App\User;
use Illuminate\Support\Facades\DB;
use Modules\BackOffice\Entities\Invoice;
use Modules\BackOffice\Entities\Termination;
use Modules\BackOffice\Events\TenantTermination;
use Modules\General\Http\Controllers\GeneralController as General;
use Modules\Masters\Entities\Building;
use Modules\Masters\Entities\Location;
use Modules\Masters\Entities\Unit;
use Modules\Masters\Entities\UnitType;
use Modules\Masters\Entities\VacantVacancyLoss;
use Modules\Sales\Entities\TenantContract;

/**
 * Tenant-termination stage transitions, extracted from
 * TenantTerminationController::tenantTerminationStage / storeGroupAssign so the
 * desktop and the mobile API write exactly the same rows. Every stage is a new
 * `termination` row that carries the previous row's inspection fields.
 */
class TerminationWorkflow
{
    public const HANDOVER_UNASSIGNED = 502;
    public const HANDOVER_ASSIGNED = 503;
    public const TAKEN_OVER = 504;
    public const TERMINATED = 505;

    /**
     * Move the contract to the next stage for $actionKey (e.g. ACPT, RESUB).
     * Returns the new row. Side effects (rent OS, dues, vacancy, invoices,
     * notifications) are the desktop's, unchanged.
     */
    public static function advance(Termination $current, string $actionKey, User $actor): Termination
    {
        $general = new General;
        $contractId = $current->contract_id;
        $stage = (int) $current->work_flow_processes_code;
        $nextProcessId = $general->nextProcessFromAction($stage, $actionKey);
        if (!$nextProcessId) {
            throw new \InvalidArgumentException("No workflow action '$actionKey' from stage $stage.");
        }
        $processAssign = $general->roleUsersFromProcess($nextProcessId, null, null, null);

        return DB::transaction(function () use ($general, $current, $contractId, $stage, $actionKey, $actor, $nextProcessId, $processAssign) {
            $today = date('Y-m-d');

            // Outstanding rent up to the termination date, minus what was received
            $sumOfReceipt = RentOutstanding::paidForContract($contractId);
            $remainAmt = totalContractRentCountCalculation($contractId, $current->termination_date);
            $osAmount = $remainAmt - $sumOfReceipt - \Modules\Masters\Services\LegalWaiver::forContract($contractId);   // legal settlement waiver is a credit
            if (isset($osAmount)) {
                TenantContract::where('id', $contractId)->update(['tenant_contract_os' => $osAmount]);
            }

            $previousFlow = Termination::where('work_flow_processes_code', $stage)->where('contract_id', $contractId)->where('termination_type', 1)->orderBy('id', 'desc')->first();
            if ($previousFlow) {
                $previousFlow->terminationUser()->update(['status' => 0]);
            }
            $previousProcess = Termination::where('work_flow_processes_code', $nextProcessId)->where('contract_id', $contractId)->where('termination_type', 1)->orderBy('id', 'desc')->first();
            if ($previousProcess) {
                $previousProcess->terminationUser()->update(['status' => 0]);
            }

            $tenantContract = TenantContract::where('id', $contractId)->first();
            $os = self::outstandingFlag($tenantContract);

            $termination = new Termination;
            $termination->contract_id = $contractId;
            $termination->termination_type = 1;
            $termination->work_flow_processes_code = $nextProcessId;
            self::carryInspectionFields($previousFlow, $termination);
            if ($actionKey === 'RESUB') { // Resubmit status to identify
                $termination->is_resubmit = 2;
            }
            $termination->os = $os;

            $assignedTo = $previousFlow->assigned_to;
            $technician = null;
            if ($nextProcessId == self::HANDOVER_ASSIGNED) {
                $assignedTo = $previousProcess?->assigned_to;
                $technician = User::where('id', $assignedTo)->first();
            }
            if (!empty($assignedTo)) {
                $termination->assigned_to = $assignedTo;
            }
            $termination->created_by = $actor->id;
            $termination->save();

            if ($processAssign != false) {
                foreach ($processAssign->assign as $val) {
                    $termination->terminationUsers()->attach($val->role_id, ['user_id' => $val->user_id]);
                }
            } else {
                $previousOrder = $general->getPreviousOrder($nextProcessId);
                if ($previousOrder != 0) {
                    $previousAssign = $general->roleUsersFromProcess($previousOrder, 0, 0, null);
                    foreach ($previousAssign->assign as $val) {
                        $termination->terminationUsers()->attach($val->role_id, ['user_id' => $val->user_id]);
                    }
                } else {
                    $workFlowProcess = $general->workFlowProcess($nextProcessId);
                    $termination->terminationUsers()->attach($workFlowProcess->default_role, ['user_id' => $workFlowProcess->default_user_id]);
                }
            }
            $termination->terminationUsers()->attach($actor->default_role, ['user_id' => $actor->id]);
            $termination->save();

            if ($nextProcessId == self::TERMINATED) {
                self::terminateContract($termination, $actor);
            }

            /* Notification */
            $process = $general->workFlowProcessNames($nextProcessId);
            clearNotification('Modules\BackOffice\Notifications\TenantTerminationNotification', $termination->id);
            readNotification('Modules\BackOffice\Notifications\TenantTerminationNotification', $termination->id);
            $termination->text = 'Tenant Termination - ' . $process->work_flow_processes_name;

            switch ($nextProcessId) {
                case self::HANDOVER_UNASSIGNED:
                    $users = User::role(['maintenance_supervisor'])->get();
                    $termination->text = 'Tenant Termination HandOver Assigned ';
                    $termination->href = url('handoverUnassigned/' . $termination->id . '/handoverUnassignedView/');
                    break;
                case self::HANDOVER_ASSIGNED:
                    $users = User::role(['maintenance_supervisor'])->get();
                    $termination->href = url('handoverAssigned/' . $termination->id . '/handoverAssignedView/');
                    $termination->text = 'Tenant Termination - ' . $process->work_flow_processes_name . ' Resubmit';
                    if ($technician) {
                        event(new TenantTermination($termination, $technician));
                    }
                    break;
                case self::TAKEN_OVER:
                    $users = User::role(['backoffice_executive'])->get();
                    $termination->href = url('takeoverForTermination/' . $termination->id . '/takeoverForTerminationView/');
                    break;
                case self::TERMINATED:
                default:
                    $users = User::role(['backoffice_executive'])->get();
                    $termination->href = url('tenantTerminatedContract/' . $termination->id . '/tenantTerminatedContractView/');
                    break;
            }
            event(new TenantTermination($termination, \Illuminate\Support\Arr::flatten($users)));

            return $termination;
        });
    }

    /** The list page users land on after advance(), per next stage (desktop redirect). */
    public static function routeAfter(int $nextProcessId): string
    {
        return match ($nextProcessId) {
            self::HANDOVER_UNASSIGNED => 'tenantTermination.index',
            self::HANDOVER_ASSIGNED => 'takeoverForTermination',
            self::TAKEN_OVER => 'handoverAssigned',
            default => 'takeoverForTermination',
        };
    }

    /** 502 → 503 for one termination: the per-row body of the desktop's storeGroupAssign. */
    public static function assignHandover(Termination $current, User $supervisor, User $actor): Termination
    {
        $workflowId = self::HANDOVER_UNASSIGNED;
        $nextProcessId = self::HANDOVER_ASSIGNED;

        return DB::transaction(function () use ($current, $supervisor, $actor, $workflowId, $nextProcessId) {
            $tenantContract = TenantContract::where('id', $current->contract_id)->first();
            $os = self::outstandingFlag($tenantContract);

            $previousFlow = Termination::where('work_flow_processes_code', $workflowId)->where('contract_id', $current->contract_id)->where('termination_type', 1)->orderBy('id', 'desc')->first();
            if ($previousFlow) {
                $previousFlow->terminationUser()->update(['status' => 0]);
            }
            $previousProcess = Termination::where('work_flow_processes_code', $nextProcessId)->where('contract_id', $current->contract_id)->where('termination_type', 1)->orderBy('id', 'desc')->first();
            if ($previousProcess) {
                $previousProcess->terminationUser()->update(['status' => 0]);
            }

            $termination = new Termination;
            $termination->contract_id = $current->contract_id;
            $termination->termination_type = 1;
            $termination->assigned_to = $supervisor->id;
            $termination->work_flow_processes_code = $nextProcessId;
            self::carryInspectionFields($current, $termination);
            $termination->termination_review_status = $current->termination_review_status;
            // the desktop takes the header dates/remark from the latest 502 row
            if ($previousFlow) {
                $termination->termination_type_status = $previousFlow->termination_type_status;
                $termination->termination_remark = $previousFlow->termination_remark;
                $termination->termination_takenover_date = $previousFlow->termination_takenover_date;
                $termination->termination_date = $previousFlow->termination_date;
            }
            $termination->os = $os;
            $termination->created_by = $actor->id;
            $termination->save();

            $termination->terminationUsers()->attach($supervisor->default_role, ['user_id' => $supervisor->id]);
            if ($actor->id !== $supervisor->id) {
                $termination->terminationUsers()->attach($actor->default_role, ['user_id' => $actor->id]);
            }
            $termination->save();

            /* Notification */
            clearNotification('Modules\BackOffice\Notifications\TenantTerminationNotification', $current->id);
            $current->text = 'Handover Assigned ';
            $current->href = url('handoverAssigned/' . $current->id . '/handoverAssignedInspection/');
            event(new TenantTermination($current, User::whereIn('id', [$supervisor->id])->get()));

            return $termination;
        });
    }

    /** Copy the inspection fields from one stage row to the next. */
    private static function carryInspectionFields(Termination $from, Termination $to): void
    {
        $to->termination_type_status = $from->termination_type_status;
        $to->termination_takenover_date = $from->termination_takenover_date;
        $to->termination_date = $from->termination_date;
        $to->termination_remark = $from->termination_remark;
        $to->termination_main_key_status = $from->termination_main_key_status;
        $to->termination_electricity_acc_no = $from->termination_electricity_acc_no;
        $to->termination_electricity_close_reading = $from->termination_electricity_close_reading;
        $to->termination_electricity_amount = $from->termination_electricity_amount;
        $to->termination_water_acc_no = $from->termination_water_acc_no;
        $to->termination_water_close_reading = $from->termination_water_close_reading;
        $to->termination_water_amount = $from->termination_water_amount;
        $to->termination_total_amount = $from->termination_total_amount;
        $to->termination_total_elec_water_amount = $from->termination_total_elec_water_amount;
        $to->termination_discount_maintenance_due = $from->termination_discount_maintenance_due;
        $to->termination_net_amount = $from->termination_net_amount;
        $to->termination_notes = $from->termination_notes;
        $to->termination_tenant_signature = $from->termination_tenant_signature;
    }

    /** "Yes" when the first unpaid period is already overdue (desktop `$os`). */
    private static function outstandingFlag(?TenantContract $tenantContract): string
    {
        if (!$tenantContract || !empty($tenantContract->tenant_contract_last_paid_date)) {
            return 'No';
        }
        $month = match ((int) $tenantContract->tenant_contract_payment_type) {
            1 => 1, 2 => 2, 3 => 3, 4 => 6, 5 => 12, default => 0,
        };
        $futurePaymentDate = $tenantContract->tenant_contract_effective_date->copy()->addMonths($month)->format('Y-m-d');
        return $futurePaymentDate < date('Y-m-d') ? 'Yes' : 'No';
    }

    /** Stage 505: close the contract, raise dues, vacate the unit, cancel future invoices. */
    private static function terminateContract(Termination $termination, User $actor): void
    {
        $contracts = TenantContract::where('id', $termination->contract_id)->first();
        TenantContract::where('id', $termination->contract_id)->update(['tenant_contract_status' => 0, 'tenant_renewal_termination_status' => 8]);

        // Outstanding rent / tax / E&W / maintenance becomes a tracked receivable
        // instead of a receipt nobody has paid. Never blocks the termination.
        try {
            (new TerminationDuesService)->createForTermination($termination, $actor->id);
        } catch (\Exception $e) {
            \Log::error('TerminationDues create failed for contract ' . $termination->contract_id . ': ' . $e->getMessage());
        }

        Unit::where('id', $contracts->unit_id)->update(['unit_vaccant_status' => 0]);

        $today = date('Y-m-d');
        $remainingInvoice = Invoice::whereIn('tenant_contract_id', [$termination->contract_id])->whereDate('tenant_invoice_date', '>=', $termination->termination_date)->pluck('id');
        if (count($remainingInvoice) > 0) {
            Invoice::whereIn('id', $remainingInvoice)->update(['tenant_invoice_cancelled_date' => $today, 'tenant_invoice_status' => 2]);
        }

        $unitInfo = Unit::where('id', $contracts->unit_id)->first();
        $building = Building::where('id', $unitInfo->building_id)->first();
        $unit_type = UnitType::where('id', $unitInfo->unit_type_id)->first();
        $location = Location::where('id', $building->location_id)->first();
        VacantVacancyLoss::create([
            'building_id' => $building->id,
            'unit_id' => $unitInfo->id,
            'building_name' => $building->building_name,
            'building_no' => $building->building_no,
            'unit_no' => $unitInfo->unit_no,
            'unit_type' => $unit_type->unit_types_name,
            'vacant_from' => $termination->termination_date,
            'vacant_to' => null,
            'location_id' => $building->location_id,
            'location_name' => $location->locations_name,
            'vacant_days' => null,
            'rent_per_month' => $contracts->tenant_contract_rent,
            'vacany_loss' => null,
            'status' => 1,
            'unit_type_id' => $unitInfo->unit_type_id,
            'created_by' => $actor->id,
        ]);
    }
}
