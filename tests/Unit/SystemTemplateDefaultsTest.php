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
            $this->assertSame($d['email_body'], R::cleanHtml($d['email_body']), "$key body survives the cleaner unchanged");
        }
    }
}
