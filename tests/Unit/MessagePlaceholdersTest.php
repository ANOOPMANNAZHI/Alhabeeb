<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Modules\BackOffice\Services\MessagePlaceholders as P;

class MessagePlaceholdersTest extends TestCase
{
    public function test_tenant_gets_common_and_tenant_placeholders()
    {
        $p = P::forTemplate('tenant');
        foreach (['today', 'company_name', 'tenant_name', 'contract_no', 'building', 'unit', 'location', 'rent', 'contract_start', 'contract_end', 'mobile'] as $k) {
            $this->assertArrayHasKey($k, $p);
        }
        $this->assertArrayNotHasKey('landlord_name', $p);
    }

    public function test_any_gets_common_only()
    {
        $this->assertSame(['company_name', 'today'], self::sorted(array_keys(P::forTemplate('any'))));
    }

    public function test_samples_are_strings_for_every_placeholder()
    {
        foreach (array_keys(P::AUDIENCES) as $a) {
            foreach (P::samples($a) as $k => $v) {
                $this->assertIsString($v, "$a.$k");
                $this->assertNotSame('', $v, "$a.$k");
            }
        }
    }

    public function test_system_key_adds_its_placeholders()
    {
        P::registerSystem('test_bounce', ['cheque_no' => ['label' => 'Cheque no.', 'sample' => '82079797']]);
        $this->assertSame('82079797', P::samples('staff', 'test_bounce')['cheque_no']);
        $this->assertArrayNotHasKey('cheque_no', P::samples('staff'));
    }

    public function test_system_key_offers_only_common_and_its_own_placeholders()
    {
        P::registerSystem('t_scope', ['cheque_no' => ['label' => 'Cheque no.', 'sample' => '1']]);
        $this->assertSame(['cheque_no', 'company_name', 'today'], self::sorted(array_keys(P::forTemplate('tenant', 't_scope'))));
    }

    public function test_blocks_are_kept_out_of_samples()
    {
        P::registerSystem('t_block', [
            'tenant_name' => ['label' => 'Tenant', 'sample' => 'Ali'],
            'details' => ['label' => 'Details (table)', 'sample' => '<table><tr><td>x</td></tr></table>', 'block' => true],
        ]);
        $this->assertArrayNotHasKey('details', P::samples('tenant', 't_block'));
        $this->assertSame('Ali', P::samples('tenant', 't_block')['tenant_name']);
        $this->assertSame(['details' => '<table><tr><td>x</td></tr></table>'], P::blockSamples('tenant', 't_block'));
    }

    public function test_unknown_audience_falls_back_to_common()
    {
        $this->assertSame(self::sorted(array_keys(P::forTemplate('any'))), self::sorted(array_keys(P::forTemplate('nope'))));
    }

    private static function sorted(array $a) { sort($a); return $a; }
}
