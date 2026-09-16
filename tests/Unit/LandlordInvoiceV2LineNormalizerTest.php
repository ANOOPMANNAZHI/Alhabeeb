<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Modules\BackOffice\Services\LandlordInvoiceV2LineNormalizer;

class LandlordInvoiceV2LineNormalizerTest extends TestCase
{
    public function test_tax_invoice_applies_five_percent_vat_per_line_and_sums_totals()
    {
        $out = LandlordInvoiceV2LineNormalizer::normalize([
            ['description' => 'MANAGEMENT FEES', 'amount' => '100.1234'],
            ['description' => 'CLEANING', 'amount' => 10],
        ], 'tax_invoice');

        $this->assertFalse($out['skip']);
        $this->assertSame(100.123, $out['lines'][0]['amount']);
        $this->assertSame(5.006, $out['lines'][0]['vat_amount']);
        $this->assertSame(0.5, $out['lines'][1]['vat_amount']);
        $this->assertSame(110.123, $out['subtotal']);
        $this->assertSame(5.506, $out['vat_total']);
        $this->assertSame(115.629, $out['grand_total']);
    }

    public function test_other_deductions_has_no_vat()
    {
        $out = LandlordInvoiceV2LineNormalizer::normalize([
            ['description' => 'MUN TAX', 'amount' => 40],
        ], 'other_deductions');

        $this->assertSame(0.0, $out['lines'][0]['vat_amount']);
        $this->assertSame(0.0, $out['vat_total']);
        $this->assertSame(40.0, $out['grand_total']);
    }

    public function test_empty_and_all_zero_sections_are_skipped()
    {
        $this->assertTrue(LandlordInvoiceV2LineNormalizer::normalize([], 'tax_invoice')['skip']);

        $out = LandlordInvoiceV2LineNormalizer::normalize([
            ['description' => 'A', 'amount' => '0'],
            ['description' => 'B', 'amount' => '0.0004'],
        ], 'tax_invoice');
        $this->assertTrue($out['skip']);
    }

    public function test_zero_rows_are_kept_when_section_has_a_non_zero_row()
    {
        $out = LandlordInvoiceV2LineNormalizer::normalize([
            5 => ['description' => 'A', 'amount' => 0],
            9 => ['description' => 'B', 'amount' => 12.5],
        ], 'other_deductions');

        $this->assertFalse($out['skip']);
        $this->assertCount(2, $out['lines']);
        $this->assertSame(1, $out['lines'][0]['line_order']);
        $this->assertSame(2, $out['lines'][1]['line_order']);
        $this->assertSame('B', $out['lines'][1]['description']);
    }

    public function test_head_is_cast_to_int_or_null_and_description_trimmed()
    {
        $out = LandlordInvoiceV2LineNormalizer::normalize([
            ['description' => '  X  ', 'amount' => 1, 'acc_codes_id' => '17'],
            ['description' => 'Y', 'amount' => 1, 'acc_codes_id' => ''],
            ['description' => 'Z', 'amount' => 1],
        ], 'tax_invoice');

        $this->assertSame('X', $out['lines'][0]['description']);
        $this->assertSame(17, $out['lines'][0]['acc_codes_id']);
        $this->assertNull($out['lines'][1]['acc_codes_id']);
        $this->assertNull($out['lines'][2]['acc_codes_id']);
    }
}
