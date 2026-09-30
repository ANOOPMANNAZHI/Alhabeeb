<?php

namespace Modules\BackOffice\Services;

/**
 * Complaint messages to the tenant (system templates complaint_registered,
 * complaint_completed, complaint_closed). Email order follows the old code:
 * contact email first for registered / closed, personal first for completed.
 */
class ComplaintMessages
{
    public static function registered($complaint)
    {
        return self::send('complaint_registered', $complaint, 'contact');
    }

    public static function completed($complaint)
    {
        return self::send('complaint_completed', $complaint, 'personal');
    }

    public static function closed($complaint)
    {
        return self::send('complaint_closed', $complaint, 'contact');
    }

    private static function send($key, $complaint, $emailFirst)
    {
        $tenant = $complaint ? $complaint->tenant : null;
        return SystemMessenger::send($key, self::tenantRecipient($tenant, $emailFirst), MessageValues::complaint($complaint),
            ['related' => ['complaint', $complaint ? $complaint->id : null]]);
    }

    /** $emailFirst: 'contact' or 'personal' — which tenant email is tried first. */
    public static function tenantRecipient($tenant, $emailFirst = 'contact')
    {
        if (!$tenant) {
            return new MessageRecipient('');
        }
        $contact = trim((string) $tenant->tenant_contact_email);
        $personal = trim((string) $tenant->tenant_personal_email);
        $email = $emailFirst === 'personal' ? ($personal !== '' ? $personal : $contact) : ($contact !== '' ? $contact : $personal);
        return new MessageRecipient($tenant->tenant_name, $email, $tenant->tenant_contact_no ?: $tenant->tenant_residence_tel);
    }
}
