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
}
