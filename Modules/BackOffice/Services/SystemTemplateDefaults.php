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
