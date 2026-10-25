<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Modules\BackOffice\Services\SystemTemplateDefaults as D;
use Modules\BackOffice\Services\MessagePlaceholders as P;
use Modules\BackOffice\Services\MessageTemplateRenderer as R;

class SystemTemplateDefaultsTest extends TestCase
{
    public function test_c1_keys_exist_with_expected_switches()
    {
        $are = D::get('pdc_bounce_are');
        $tenant = D::get('pdc_bounce_tenant');
        $this->assertSame('staff', $are['audience']);
        $this->assertTrue($are['is_active']);
        $this->assertFalse($are['sms_enabled']);
        $this->assertSame('tenant', $tenant['audience']);
        $this->assertFalse($tenant['is_active']);
        $this->assertNull(D::get('nope'));
    }

    public function test_c2_keys_exist_with_expected_switches()
    {
        $keys = ['complaint_registered', 'complaint_completed', 'complaint_closed', 'key_accepted_tenant', 'key_accepted_landlord',
                 'renewal_form', 'inspection_details', 'termination_details', 'tenant_birthday', 'pdc_due_reminder'];
        foreach ($keys as $key) {
            $this->assertNotNull(D::get($key), $key);
            $this->assertSame('C2', D::get($key)['stage'], $key);
        }
        $this->assertFalse(D::get('termination_details')['is_active']);
        $reminder = D::get('pdc_due_reminder');
        $this->assertTrue($reminder['sms_enabled']);
        $this->assertNull($reminder['email_subject']);
        $this->assertNull($reminder['email_body']);
        $this->assertStringContainsString('{contract_end}', D::get('renewal_form')['email_body']);
        $this->assertStringContainsString('{inspection_details}', D::get('inspection_details')['email_body']);
        $this->assertTrue(D::get('inspection_details')['placeholders']['inspection_details']['block']);
        $this->assertSame('landlord', D::get('key_accepted_landlord')['audience']);
        foreach ($keys as $key) {
            if ($key !== 'pdc_due_reminder') {
                $this->assertFalse(D::get($key)['sms_enabled'], "$key SMS off by default");
            }
        }
    }

    public function test_c3_keys_exist_with_expected_switches()
    {
        $legal = ['legal_deposit_refund_posted', 'legal_rental_income_posted', 'legal_pdc_posted', 'legal_receipt_posted', 'legal_contract_revoked',
                  'legal_case_received', 'legal_case_approved', 'legal_case_referback', 'legal_advisor_approved', 'legal_advisor_referback',
                  'legal_case_closed', 'legal_advisor_closed', 'legal_status_note'];
        $keys = array_merge(['complaint_assigned', 'complaint_subassigned', 'enquiry_new', 'enquiry_assigned', 'enquiry_reassigned', 'enquiry_rejected'], $legal);
        foreach ($keys as $key) {
            $d = D::get($key);
            $this->assertNotNull($d, $key);
            $this->assertSame('C3', $d['stage'], $key);
            $this->assertSame(in_array($key, ['complaint_assigned', 'complaint_subassigned'], true), $d['sms_enabled'], "$key SMS default");
            $this->assertSame(!in_array($key, ['legal_case_referback', 'legal_advisor_approved'], true), $d['is_active'], "$key active default");
        }
        $this->assertSame('any', D::get('enquiry_new')['audience']);
        $this->assertStringContainsString('{invoice_no}', D::get('legal_rental_income_posted')['email_body']);
        $this->assertStringContainsString('{cheque_no}', D::get('legal_pdc_posted')['email_body']);
        $this->assertStringContainsString('{receipt_no}', D::get('legal_receipt_posted')['email_body']);
        $this->assertStringContainsString('{status_note}', D::get('legal_status_note')['email_body']);
        $this->assertStringContainsString('{works_code}', D::get('complaint_assigned')['sms_body']);
    }

    public function test_every_default_is_complete_and_uses_only_known_placeholders()
    {
        D::registerPlaceholders();
        foreach (D::all() as $key => $d) {
            $this->assertNotEmpty($d['name'], $key);
            $this->assertArrayHasKey($d['audience'], P::AUDIENCES, $key);
            $this->assertTrue(($d['email_subject'] && $d['email_body']) || $d['sms_body'], "$key has a channel");
            $known = array_keys(P::forTemplate($d['audience'], $key));
            foreach ([$d['email_subject'], $d['email_body'], $d['sms_body']] as $text) {
                foreach (R::placeholdersIn($text) as $ph) {
                    $this->assertStringContainsString($ph, $known, "$key uses unregistered {{$ph}}");
                }
            }
            if ($d['email_body'] !== null) {
                $this->assertSame($d['email_body'], R::cleanHtml($d['email_body']), "$key body survives the cleaner unchanged");
            }
        }
    }

    public function test_c4_keys_exist_with_expected_switches()
    {
        foreach (['employee_welcome', 'employee_password_reset', 'password_forgot', 'are_residence_reminder'] as $key) {
            $d = D::get($key);
            $this->assertNotNull($d, $key);
            $this->assertSame('C4', $d['stage'], $key);
            $this->assertSame('staff', $d['audience'], $key);
            $this->assertFalse($d['sms_enabled'], "$key SMS off");
        }
        $this->assertTrue(D::get('employee_welcome')['is_active']);
        $this->assertTrue(D::get('employee_password_reset')['is_active']);
        $this->assertTrue(D::get('password_forgot')['is_active']);
        $this->assertFalse(D::get('are_residence_reminder')['is_active']);
        $this->assertStringContainsString('href="{set_password_link}"', D::get('employee_welcome')['email_body']);
        $this->assertStringContainsString('{username}', D::get('employee_welcome')['email_body']);
        $this->assertStringContainsString('href="{set_password_link}"', D::get('employee_password_reset')['email_body']);
        $this->assertStringContainsString('href="{reset_link}"', D::get('password_forgot')['email_body']);
        $this->assertStringContainsString('{residence_expiry}', D::get('are_residence_reminder')['email_body']);
        $this->assertNull(D::get('employee_welcome')['sms_body'], 'no password link by SMS');
        $this->assertNull(D::get('password_forgot')['sms_body']);
    }

    public function test_password_links_last_24_hours()
    {
        $config = require __DIR__ . '/../../config/auth.php';
        $this->assertSame(1440, $config['passwords']['users']['expire']);
    }
}
