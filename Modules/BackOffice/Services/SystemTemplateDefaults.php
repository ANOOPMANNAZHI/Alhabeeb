<?php

namespace Modules\BackOffice\Services;

use Illuminate\Support\Facades\DB;

/**
 * Default wording for every system template (project C). Seeds the
 * message_templates rows, stands in when a row is missing, powers
 * "Reset to default" and registers each key's placeholders.
 */
class SystemTemplateDefaults
{
    // Placeholder sets. A system template offers {today}, {company_name} and exactly
    // the sets listed in its 'placeholders' — the values its send site fills in.

    private static function tenantPlaceholders()
    {
        return [
            'tenant_name' => ['label' => 'Tenant name', 'sample' => 'Mohammed Al Balushi'],
            'mobile'      => ['label' => 'Tenant mobile', 'sample' => '99123456'],
        ];
    }

    /** MessageValues::contract() fills these (and tenant_name, mobile). */
    private static function contractPlaceholders()
    {
        return [
            'contract_no'    => ['label' => 'Contract no.', 'sample' => 'TAG2601977'],
            'building'       => ['label' => 'Building', 'sample' => 'Al Qandeel-2 Bldg'],
            'unit'           => ['label' => 'Unit', 'sample' => '84'],
            'location'       => ['label' => 'Location', 'sample' => 'Al Khuwair'],
            'rent'           => ['label' => 'Monthly rent', 'sample' => '250.000'],
            'contract_start' => ['label' => 'Contract start', 'sample' => '15/09/2026'],
            'contract_end'   => ['label' => 'Contract end', 'sample' => '30/09/2027'],
        ];
    }

    /** MessageValues::cheque() fills these. */
    private static function chequePlaceholders()
    {
        return [
            'cheque_no'     => ['label' => 'Cheque no.', 'sample' => '82079797'],
            'amount'        => ['label' => 'Cheque amount', 'sample' => '250.000'],
            'cheque_date'   => ['label' => 'Cheque date', 'sample' => '15/09/2026'],
            'bounce_reason' => ['label' => 'Bounce reason', 'sample' => 'Insufficient Funds'],
            'remarks'       => ['label' => 'Remarks', 'sample' => 'FUNDS NOT SUFFICIENT'],
        ];
    }

    private static function employeePlaceholders()
    {
        return ['employee_name' => ['label' => 'Employee name', 'sample' => 'Rashid Al Harthy']];
    }

    public static function all()
    {
        return [
            'pdc_bounce_are' => [
                'name' => 'Cheque bounced (to ARE)', 'audience' => 'staff', 'stage' => 'C1',
                'email_subject' => 'Cheque bounced - {tenant_name} ({cheque_no})',
                'email_body' => '<p>Dear {employee_name},</p>'
                    . '<p>This is to inform you that cheque <strong>{cheque_no}</strong> for OMR {amount} dated {cheque_date} has bounced.</p>'
                    . '<p>Tenant: {tenant_name}<br>Contract: {contract_no}<br>Building: {building}, unit {unit}</p>'
                    . '<p>Bounce reason: {bounce_reason}<br>Remarks: {remarks}</p>'
                    . '<p>Regards,<br>Al Habib Team</p>',
                'sms_body' => 'Cheque {cheque_no} of {tenant_name} ({building} {unit}) bounced: {bounce_reason}. - Al Habib',
                'is_active' => true, 'sms_enabled' => false,
                'placeholders' => self::employeePlaceholders() + self::tenantPlaceholders() + self::contractPlaceholders() + self::chequePlaceholders(),
            ],
            'pdc_bounce_tenant' => [
                'name' => 'Cheque bounced (to tenant)', 'audience' => 'tenant', 'stage' => 'C1',
                'email_subject' => 'Your cheque {cheque_no} has been returned by the bank',
                'email_body' => '<p>Dear {tenant_name},</p>'
                    . '<p>We regret to inform you that your cheque no. <strong>{cheque_no}</strong> for OMR {amount} dated {cheque_date} '
                    . 'for {building}, unit {unit} (contract {contract_no}) was returned by the bank.</p>'
                    . '<p>Reason: {bounce_reason}</p>'
                    . '<p>Please contact our office at the earliest to replace the cheque and avoid penalty charges.</p>'
                    . '<p>Regards,<br>Al Habib Team</p>',
                'sms_body' => 'Dear {tenant_name}, your cheque {cheque_no} for OMR {amount} was returned by the bank ({bounce_reason}). Please contact Al Habib to replace it.',
                'is_active' => false, 'sms_enabled' => false,
                'placeholders' => self::tenantPlaceholders() + self::contractPlaceholders() + self::chequePlaceholders(),
            ],
        ] + self::c2();
    }

    private static function complaintPlaceholders()
    {
        return [
            'complaint_no'   => ['label' => 'Complaint no.', 'sample' => 'CMP2609123'],
            'complaint_date' => ['label' => 'Complaint date', 'sample' => '29/09/2026'],
            'building'       => ['label' => 'Building', 'sample' => 'Al Qandeel-2 Bldg'],
            'unit'           => ['label' => 'Unit', 'sample' => '84'],
            'location'       => ['label' => 'Location', 'sample' => 'Al Khuwair'],
        ] + self::tenantPlaceholders();
    }

    private static function placePlaceholders()
    {
        return [
            'building' => ['label' => 'Building', 'sample' => 'Al Qandeel-2 Bldg'],
            'unit'     => ['label' => 'Unit', 'sample' => '84'],
        ];
    }

    private static function tableSample($title)
    {
        return '<table border="1" style="border-collapse:collapse"><tr><th>' . $title . '</th><th>Amount</th></tr>'
            . '<tr><td>Painting</td><td>45.000</td></tr><tr><td>Cleaning</td><td>15.000</td></tr></table>';
    }

    private static function regards()
    {
        return '<p>Regards,<br>Al Habib Team</p>';
    }

    /** C2: tenant and landlord messages. */
    private static function c2()
    {
        $complaint = self::complaintPlaceholders();
        $contract = self::tenantPlaceholders() + self::contractPlaceholders();
        return [
            'complaint_registered' => [
                'name' => 'Complaint registered', 'audience' => 'tenant', 'stage' => 'C2',
                'email_subject' => 'Complaint {complaint_no} registered',
                'email_body' => '<p>Dear {tenant_name},</p>'
                    . '<p>We have received your complaint no. <strong>{complaint_no}</strong> for {building}, unit {unit}. '
                    . 'Our maintenance team will get back to you at the earliest.</p>' . self::regards(),
                'sms_body' => 'Dear {tenant_name}, your complaint {complaint_no} has been registered. Our maintenance team will contact you soon. - Al Habib',
                'is_active' => true, 'sms_enabled' => false, 'placeholders' => $complaint,
            ],
            'complaint_completed' => [
                'name' => 'Complaint completed', 'audience' => 'tenant', 'stage' => 'C2',
                'email_subject' => 'Complaint {complaint_no} completed',
                'email_body' => '<p>Dear {tenant_name},</p>'
                    . '<p>Your complaint no. <strong>{complaint_no}</strong> has been successfully attended by the Al Habib Maintenance Team. '
                    . 'Thank you for your kind co-operation.</p>' . self::regards(),
                'sms_body' => 'Dear {tenant_name}, your complaint {complaint_no} has been completed by Al Habib Maintenance. Thank you for your co-operation.',
                'is_active' => true, 'sms_enabled' => false, 'placeholders' => $complaint,
            ],
            'complaint_closed' => [
                'name' => 'Complaint closed', 'audience' => 'tenant', 'stage' => 'C2',
                'email_subject' => 'Complaint {complaint_no} closed',
                'email_body' => '<p>Dear {tenant_name},</p>'
                    . '<p>Our team has completed the work on your complaint no. <strong>{complaint_no}</strong>. '
                    . 'If you have any questions about the work or service, please do not hesitate to contact us.</p>'
                    . '<p>We look forward to serving you again.</p>' . self::regards(),
                'sms_body' => 'Dear {tenant_name}, your complaint {complaint_no} has been closed. Contact Al Habib if you have any questions.',
                'is_active' => true, 'sms_enabled' => false, 'placeholders' => $complaint,
            ],
            'key_accepted_tenant' => [
                'name' => 'Keys received (from tenant)', 'audience' => 'tenant', 'stage' => 'C2',
                'email_subject' => 'Keys received - {building}, unit {unit}',
                'email_body' => '<p>Dear {tenant_name},</p>'
                    . '<p>This is to confirm that we have received the keys of {building}, unit {unit} on {today}.</p>'
                    . '<p>Thank you.</p>' . self::regards(),
                'sms_body' => 'Dear {tenant_name}, we have received the keys of {building}, unit {unit} on {today}. - Al Habib',
                'is_active' => true, 'sms_enabled' => false,
                'placeholders' => ['tenant_name' => self::tenantPlaceholders()['tenant_name']] + self::placePlaceholders(),
            ],
            'key_accepted_landlord' => [
                'name' => 'Keys received (from landlord)', 'audience' => 'landlord', 'stage' => 'C2',
                'email_subject' => 'Keys received - {building}, unit {unit}',
                'email_body' => '<p>Dear {landlord_name},</p>'
                    . '<p>This is to confirm that we have received the keys of {building}, unit {unit} on {today}.</p>'
                    . '<p>Thank you.</p>' . self::regards(),
                'sms_body' => 'Dear {landlord_name}, we have received the keys of {building}, unit {unit} on {today}. - Al Habib',
                'is_active' => true, 'sms_enabled' => false,
                'placeholders' => ['landlord_name' => ['label' => 'Landlord name', 'sample' => 'Ali Mallalah Habib']] + self::placePlaceholders(),
            ],
            'renewal_form' => [
                'name' => 'Tenancy renewal form (PDF attached)', 'audience' => 'tenant', 'stage' => 'C2',
                'email_subject' => 'Tenancy renewal form - {building}, unit {unit}',
                'email_body' => '<p>Dear {tenant_name},</p>'
                    . '<p>This is an official notice to inform you that your current lease agreement for {building} / {unit} is due for renewal on {contract_end}. '
                    . 'Accordingly, we enclose herewith the Online Tenancy Contract Renewal Form for your signature.</p>'
                    . '<p>You are requested to submit the form duly signed, along with the rental cheques and copies of ID card / company documents (as applicable), '
                    . 'at our office, which will enable us to proceed with the online registration at Muscat Municipality.</p>'
                    . '<p>We request you to finish the renewal process within a week from the receipt of this mail. As you are aware, late registration attracts a penalty, '
                    . 'so please complete the formalities well in time. You may contact us if you need any clarification.</p>'
                    . '<p>Thank you for your co-operation.</p>' . self::regards(),
                'sms_body' => 'Dear {tenant_name}, your lease for {building} / {unit} is due for renewal on {contract_end}. The renewal form has been emailed to you. - Al Habib',
                'is_active' => true, 'sms_enabled' => false, 'placeholders' => $contract,
            ],
            'inspection_details' => [
                'name' => 'Inspection report (termination)', 'audience' => 'tenant', 'stage' => 'C2',
                'email_subject' => 'Inspection report - contract {contract_no}',
                'email_body' => '<p>Dear {tenant_name},</p>'
                    . '<p>Please find below the inspection report for your contract no. <strong>{contract_no}</strong> ({building}, unit {unit}).</p>'
                    . '{inspection_details}' . self::regards(),
                'sms_body' => 'Dear {tenant_name}, the inspection report for contract {contract_no} has been emailed to you. - Al Habib',
                'is_active' => true, 'sms_enabled' => false,
                'placeholders' => $contract + ['inspection_details' => ['label' => 'Inspection details (table)', 'sample' => self::tableSample('Work'), 'block' => true]],
            ],
            'termination_details' => [
                'name' => 'Termination inspection details', 'audience' => 'tenant', 'stage' => 'C2',
                'email_subject' => 'Termination inspection details - contract {contract_no}',
                'email_body' => '<p>Dear {tenant_name},</p>'
                    . '<p>This is your official notice of the inspection details for contract no. <strong>{contract_no}</strong> ({building}, unit {unit}).</p>'
                    . '{termination_details}' . self::regards(),
                'sms_body' => 'Dear {tenant_name}, the termination inspection details for contract {contract_no} have been emailed to you. - Al Habib',
                'is_active' => false, 'sms_enabled' => false,
                'placeholders' => $contract + ['termination_details' => ['label' => 'Termination details (table)', 'sample' => self::tableSample('Item'), 'block' => true]],
            ],
            'tenant_birthday' => [
                'name' => 'Tenant birthday', 'audience' => 'tenant', 'stage' => 'C2',
                'email_subject' => 'Happy Birthday',
                'email_body' => '<p>Dear {tenant_name},</p>'
                    . '<p>Wish you a Happy Birthday! We hope that you have a great year and accomplish all the fabulous goals you have set. '
                    . 'May the coming years be filled with happiness, peace and love. Have a great day ahead.</p>' . self::regards(),
                'sms_body' => 'Dear {tenant_name}, Al Habib wishes you a very Happy Birthday!',
                'is_active' => true, 'sms_enabled' => false,
                'placeholders' => ['tenant_name' => self::tenantPlaceholders()['tenant_name']],
            ],
            'pdc_due_reminder' => [
                'name' => 'Cheque due reminder (SMS)', 'audience' => 'tenant', 'stage' => 'C2',
                'email_subject' => null, 'email_body' => null,
                'sms_body' => 'Dear {tenant_name}, your cheque No. {cheque_no} of amount {amount} is due on {cheque_date}. '
                    . 'Please ensure sufficient funds are available in your account to avoid penalty charges. - Al Habib',
                'is_active' => true, 'sms_enabled' => true,
                'placeholders' => ['tenant_name' => self::tenantPlaceholders()['tenant_name'], 'contract_no' => self::contractPlaceholders()['contract_no']]
                    + self::placePlaceholders() + array_intersect_key(self::chequePlaceholders(), array_flip(['cheque_no', 'amount', 'cheque_date'])),
            ],
        ];
    }

    public static function get($key)
    {
        $all = self::all();
        return isset($all[$key]) ? $all[$key] : null;
    }

    public static function registerPlaceholders()
    {
        foreach (self::all() as $key => $d) {
            MessagePlaceholders::registerSystem($key, $d['placeholders']);
        }
    }

    public static function seed(array $keys)
    {
        $inserted = 0;
        foreach ($keys as $key) {
            $d = self::get($key);
            if (!$d || DB::table('message_templates')->where('key', $key)->exists()) {
                continue;
            }
            DB::table('message_templates')->insert([
                'key' => $key, 'name' => $d['name'], 'kind' => 'system', 'audience' => $d['audience'],
                'email_subject' => $d['email_subject'], 'email_body' => $d['email_body'], 'sms_body' => $d['sms_body'],
                'is_active' => $d['is_active'], 'sms_enabled' => $d['sms_enabled'],
                'created_at' => now(), 'updated_at' => now(),
            ]);
            $inserted++;
        }
        return $inserted;
    }
}
