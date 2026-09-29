<?php

namespace Modules\BackOffice\Services;

use Modules\Masters\Entities\Vendor;
use Modules\Sales\Entities\Tenant;

/** Tenant- and landlord-facing system messages (C2), one method per send site. */
class TenantMessages
{
    /** Keys handed back by a tenant (key->tenant_id) or a landlord (key->landlord_id). */
    public static function keyAccepted($key)
    {
        $related = ['related' => ['key', $key->id]];
        $place = MessageValues::place($key->building_id, $key->unit_id);
        if ($key->tenant_id) {
            $tenant = Tenant::find($key->tenant_id);
            return SystemMessenger::send('key_accepted_tenant', ComplaintMessages::tenantRecipient($tenant, 'contact'),
                $place + MessageValues::tenant($tenant), $related);
        }
        $vendor = $key->landlord_id ? Vendor::find($key->landlord_id) : null;
        $to = $vendor ? new MessageRecipient($vendor->vendor_name, $vendor->vendor_contact_email, $vendor->vendor_contact_no) : new MessageRecipient('');
        return SystemMessenger::send('key_accepted_landlord', $to, $place + MessageValues::vendor($vendor), $related);
    }

    /** Inspection report (take-over supervisor → tenant). @return string|null email status */
    public static function inspection($termination, $contract)
    {
        return self::terminationMessage('inspection_details', 'inspection_details', $termination, $contract);
    }

    /** Termination inspection details (arrives switched off). @return string|null email status */
    public static function terminationDetails($termination, $contract)
    {
        return self::terminationMessage('termination_details', 'termination_details', $termination, $contract);
    }

    private static function terminationMessage($key, $block, $termination, $contract)
    {
        $html = view('backoffice::Email.partials.' . $block, [
            'termination' => $termination,
            'tenantContract' => $contract,
            'terminationChecklistOther' => $contract->terminationChecklistOther,
            'groupedWork' => $contract->terminationChecklist->groupBy('work_id'),
        ])->render();
        $r = SystemMessenger::send($key, ComplaintMessages::tenantRecipient($contract->tenant, 'contact'), MessageValues::contract($contract), [
            'blocks' => [$block => $html],
            'related' => ['termination', $termination->id],
        ]);
        return $r['email'];
    }

    /** Daily 10:00 birthday wish; personal email first (as before). */
    public static function birthday($tenant)
    {
        return SystemMessenger::send('tenant_birthday', ComplaintMessages::tenantRecipient($tenant, 'personal'),
            MessageValues::tenant($tenant), ['related' => ['tenant', $tenant->id]]);
    }

    /**
     * Daily 10:00 SMS for a rent cheque due in 5 days, to the tenant's contact number only (as before).
     * @return string|null SMS status; null when the template's Send SMS switch is off
     */
    public static function pdcDueReminder($pdc)
    {
        $contract = $pdc->tenantContractInfo;
        $tenant = $contract ? $contract->tenant : null;
        $to = new MessageRecipient($tenant ? $tenant->tenant_name : '', null, $tenant ? $tenant->tenant_contact_no : null);
        $r = SystemMessenger::send('pdc_due_reminder', $to, MessageValues::contract($contract) + MessageValues::cheque($pdc),
            ['related' => ['pdc', $pdc->id]]);
        return $r['sms'] !== null ? $r['sms'] : ($r['email'] === \Modules\BackOffice\Entities\MessageLog::SKIPPED_OFF ? $r['email'] : null);
    }

    /** What to tell the user after a manual send (flash type + text). */
    public static function flashFor($status)
    {
        switch ($status) {
            case \Modules\BackOffice\Entities\MessageLog::SENT:
                return ['success', ' Mail Send Successfully'];
            case \Modules\BackOffice\Entities\MessageLog::SKIPPED_NO_ADDRESS:
                return ['error', 'Tenant email not found.'];
            case \Modules\BackOffice\Entities\MessageLog::SKIPPED_OFF:
                return ['error', 'This email is switched off in Mail > Templates.'];
            default:
                return ['error', 'The email could not be sent (see Mail > Sent log).'];
        }
    }

    /**
     * Renewal form to the tenant with the PDF attached; cc = sender and ARE.
     * @return string|null email status (MessageLog::SENT etc.)
     */
    public static function renewalForm($contract, $pdfData, $pdfName, array $cc)
    {
        $to = ComplaintMessages::tenantRecipient($contract->tenant, 'contact');
        $to = new MessageRecipient($to->name, $to->email, $to->mobile, $cc);
        $r = SystemMessenger::send('renewal_form', $to, MessageValues::contract($contract), [
            'attachments' => [['data' => $pdfData, 'name' => $pdfName, 'mime' => 'application/pdf']],
            'related' => ['contract', $contract->id],
        ]);
        return $r['email'];
    }
}
