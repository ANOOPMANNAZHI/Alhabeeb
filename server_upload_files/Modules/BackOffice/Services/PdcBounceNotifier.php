<?php

namespace Modules\BackOffice\Services;

use Modules\BackOffice\Entities\Pdc;
use Modules\BackOffice\Events\ChequeBounceAre;

/**
 * A bounced PDC: every ARE assigned to the building gets the pdc_bounce_are
 * template (each user once), the tenant gets pdc_bounce_tenant (arrives
 * switched off), and the in-app alert goes to all those AREs.
 */
class PdcBounceNotifier
{
    /**
     * pdc_cancel_reason before and after a save: only a change to 1 (bounce) is a new bounce.
     * The PDC grid re-posts already-bounced rows on every save, which must not notify again.
     */
    public static function isNewBounce($before, $after)
    {
        return (int) $after === 1 && (int) $before !== 1;
    }

    /**
     * @param string   $href            link for the in-app alert
     * @param int|null $notificationId  id stored on the in-app alert (some screens use the contract id)
     * @return array ['ares' => number of ARE users messaged, 'tenant' => email status]
     */
    public static function notify(Pdc $pdc, $href, $notificationId = null)
    {
        $contract = $pdc->tenantContractInfo;
        $building = $contract ? $contract->building : null;
        $related = ['related' => ['pdc', $pdc->id]];
        $values = MessageValues::contract($contract) + MessageValues::cheque($pdc);

        $ares = collect();
        if ($building) {
            foreach ($building->buildingAssignTo as $assign) {
                $user = $assign->buildingAssignToName ? $assign->buildingAssignToName->areUser : null;
                if ($user) {
                    $ares->push($user);
                }
            }
        }
        $ares = $ares->unique('id')->values();

        foreach ($ares as $user) {
            $employee = $user->employee;
            $mobile = $employee ? ($employee->employee_contact_no ?: $employee->employee_secondary_no) : null;
            SystemMessenger::send('pdc_bounce_are',
                new MessageRecipient($employee && $employee->exists && $employee->employee_name ? $employee->employee_name : $user->username, $user->email, $mobile),
                $values + MessageValues::employee($user), $related);
        }

        $tenant = $contract ? $contract->tenant : null;
        $tenantResult = SystemMessenger::send('pdc_bounce_tenant',
            new MessageRecipient($tenant ? $tenant->tenant_name : '',
                $tenant ? ($tenant->tenant_personal_email ?: $tenant->tenant_contact_email) : null,
                $tenant ? ($tenant->tenant_contact_no ?: $tenant->tenant_residence_tel) : null),
            $values, $related);

        if ($ares->count()) {
            $pdc->href = $href;
            if ($notificationId !== null) {
                $pdc->id = $notificationId;
            }
            event(new ChequeBounceAre($pdc, $ares));
        }

        return ['ares' => $ares->count(), 'tenant' => $tenantResult['email']];
    }

    /**
     * A new tenant's cheque bounced (first contract): the sales person who handled the contract's
     * sales enquiry gets pdc_bounce_sales and an in-app alert. Nothing for renewals or older tenants.
     * Failures are logged only, so the bounce itself never fails.
     *
     * @return bool whether a sales person was notified
     */
    public static function notifySalesPerson($pdcId)
    {
        try {
            $pdc = Pdc::with('tenantContractInfo.tenant', 'tenantContractInfo.building', 'tenantContractInfo.Unit')->find($pdcId);
            $contract = $pdc ? $pdc->tenantContractInfo : null;
            if (!$contract || !$contract->sale_enquiry_id || !Pdc::where('id', $pdc->id)->onNewTenantContract()->exists()) {
                return false;
            }
            $userId = \DB::table('sales_enquiries')->where('id', $contract->sale_enquiry_id)->value('assigned_person');
            $user = $userId ? \App\User::where('id', $userId)->where('user_type_status', 1)->first() : null;
            if (!$user) {
                return false;
            }

            $values = MessageValues::contract($contract) + MessageValues::cheque($pdc) + MessageValues::employee($user)
                + ['bounce_list_link' => route('getBouncedCheques')];
            foreach (StaffMessages::userRecipients([$user]) as $pair) {
                SystemMessenger::send('pdc_bounce_sales', $pair[1], $values, ['related' => ['pdc', $pdc->id]]);
            }

            $alert = clone $pdc;
            $alert->text = 'New tenant cheque bounced - ' . $pdc->pdc_check_no . ' of ' . ($contract->tenant->tenant_name ?? '')
                . ' (' . $contract->tenant_contract_no . ')' . ($pdc->pdc_cancel_reason_name ? '. Reason: ' . $pdc->pdc_cancel_reason_name : '');
            $alert->href = route('getBouncedCheques');
            $alert->icon = 'fa-exclamation-circle';
            $alert->icon_color = 'red-bgcolor';
            \Illuminate\Support\Facades\Notification::send(collect([$user]), new \Modules\BackOffice\Notifications\ChequeBounceNotification($alert));
            return true;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('New tenant bounce alert to sales person failed for PDC #' . $pdcId . ': ' . $e->getMessage());
            return false;
        }
    }
}
