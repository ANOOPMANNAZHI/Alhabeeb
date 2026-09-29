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
