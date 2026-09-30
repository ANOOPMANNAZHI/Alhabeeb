<?php

namespace Modules\BackOffice\Services;

/** Staff-facing system messages (C3): complaint assignment and sales enquiries. */
class StaffMessages
{
    /** PLMS users (App\User) as recipients: one per user id, employee name and mobile. */
    public static function userRecipients($users)
    {
        $out = [];
        foreach (collect($users)->filter()->unique('id') as $user) {
            $employee = $user->employee;
            $hasEmployee = $employee && $employee->exists;
            $out[] = [$user, new MessageRecipient(
                $hasEmployee && $employee->employee_name ? $employee->employee_name : $user->username,
                $user->email,
                $hasEmployee ? ($employee->employee_contact_no ?: $employee->employee_secondary_no) : null
            )];
        }
        return $out;
    }

    /** complaint_assigned / complaint_subassigned to the technician(s). */
    public static function complaintAssigned($key, $complaint, $checklist, $users)
    {
        $values = MessageValues::assignment($checklist, $complaint);
        foreach (self::userRecipients($users) as $pair) {
            SystemMessenger::send($key, $pair[1], $values + MessageValues::employee($pair[0]), ['related' => ['complaint', $complaint->id]]);
        }
    }

    /** enquiry_new to the person who made the enquiry. */
    public static function enquiryNew($enquiry)
    {
        $to = new MessageRecipient($enquiry->sales_enquiry_name, $enquiry->sales_email, $enquiry->sales_mobile_no ?: $enquiry->alternative_no);
        return SystemMessenger::send('enquiry_new', $to, MessageValues::enquiry($enquiry), ['related' => ['enquiry', $enquiry->id]]);
    }

    /** enquiry_assigned / enquiry_reassigned / enquiry_rejected to staff users. */
    public static function enquiryToStaff($key, $enquiry, $users)
    {
        $values = MessageValues::enquiry($enquiry);
        foreach (self::userRecipients($users) as $pair) {
            SystemMessenger::send($key, $pair[1], $values + MessageValues::employee($pair[0]), ['related' => ['enquiry', $enquiry->id]]);
        }
    }

    /**
     * are_residence_reminder: one message per ARE per expired tenant/contract.
     * The source query joins ARE assignments, which repeats rows; duplicates are sent once.
     */
    public static function areResidenceReminder($rows)
    {
        // Switched off: no sends and no skipped_off log rows (the cron runs every day).
        if (!SystemTemplateDefaults::isActive('are_residence_reminder')) {
            return 0;
        }
        $done = [];
        $sent = 0;
        foreach ($rows as $r) {
            if (empty($r->are_user_id)) {
                continue;
            }
            $k = $r->are_user_id . '|' . $r->tenant_id . '|' . $r->tenant_contract_no;
            if (isset($done[$k])) {
                continue;
            }
            $done[$k] = true;
            $to = new MessageRecipient($r->employee_name, $r->are_email, $r->employee_contact_no);
            SystemMessenger::send('are_residence_reminder', $to, [
                'employee_name'    => (string) $r->employee_name,
                'tenant_name'      => (string) $r->tenant_name,
                'contract_no'      => (string) $r->tenant_contract_no,
                'building'         => (string) $r->building_name,
                'residence_expiry' => $r->tenant_resident_exp_date ? date('d/m/Y', strtotime($r->tenant_resident_exp_date)) : '',
            ], ['related' => ['tenant', $r->tenant_id]]);
            $sent++;
        }
        return $sent;
    }
}
