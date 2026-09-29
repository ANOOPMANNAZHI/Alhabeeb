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
        $this->assertContains('{contract_end}', D::get('renewal_form')['email_body']);
        $this->assertContains('{inspection_details}', D::get('inspection_details')['email_body']);
        $this->assertTrue(D::get('inspection_details')['placeholders']['inspection_details']['block']);
        $this->assertSame('landlord', D::get('key_accepted_landlord')['audience']);
        foreach ($keys as $key) {
            if ($key !== 'pdc_due_reminder') {
                $this->assertFalse(D::get($key)['sms_enabled'], "$key SMS off by default");
            }
        }
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
                    $this->assertContains($ph, $known, "$key uses unregistered {{$ph}}");
                }
            }
            if ($d['email_body'] !== null) {
                $this->assertSame($d['email_body'], R::cleanHtml($d['email_body']), "$key body survives the cleaner unchanged");
            }
        }
    }
}
