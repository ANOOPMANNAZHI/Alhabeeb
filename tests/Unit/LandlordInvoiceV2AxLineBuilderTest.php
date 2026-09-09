<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Modules\BackOffice\Services\LandlordInvoiceV2AxLineBuilder;

if (!defined('LANDLORD_INV_JOURNAL_NAME')) {
    require_once __DIR__ . '/../../config/constants.php';
}

class LandlordInvoiceV2AxLineBuilderTest extends TestCase
{
    private function facts(array $overrides = [])
    {
        return array_merge([
            'journal_num'     => 'J000123',
            'invoice_no'      => 'LTI2600007',
            'invoice_date'    => '2026-09-09',
            'invoice_type'    => 'tax_invoice',
            'description'     => 'Tax Invoice LTI2600007 ACME 09/2026',
            'vendor_code'     => 'V00042',
            'expense_account' => '12306',
            'vat_account'     => '21500',
            'building_code'   => 'B017',
            'ax_division'     => '02',
            'subtotal'        => 100.000,
            'vat_total'       => 5.000,
            'grand_total'     => 105.000,
        ], $overrides);
    }

    public function test_tax_invoice_produces_vendor_credit_expense_debit_and_vat_debit()
    {
        $lines = LandlordInvoiceV2AxLineBuilder::build($this->facts());

        $this->assertCount(3, $lines);

        $this->assertSame('VENDOR', $lines[0]['accountType']);
        $this->assertSame('V00042', $lines[0]['vendAccount']);
        $this->assertSame(0.0, $lines[0]['AmountDebit']);
        $this->assertSame(105.0, $lines[0]['AmountCredit']);

        $this->assertSame('LEDGER', $lines[1]['accountType']);
        $this->assertSame('12306', $lines[1]['vendAccount']);
        $this->assertSame(100.0, $lines[1]['AmountDebit']);
        $this->assertSame(0.0, $lines[1]['AmountCredit']);

        $this->assertSame('LEDGER', $lines[2]['accountType']);
        $this->assertSame('21500', $lines[2]['vendAccount']);
        $this->assertSame(5.0, $lines[2]['AmountDebit']);
        $this->assertSame(0.0, $lines[2]['AmountCredit']);
    }

    public function test_other_deductions_reverses_every_debit_and_credit()
    {
        $lines = LandlordInvoiceV2AxLineBuilder::build($this->facts(['invoice_type' => 'other_deductions']));

        $this->assertSame(105.0, $lines[0]['AmountDebit']);
        $this->assertSame(0.0, $lines[0]['AmountCredit']);
        $this->assertSame(0.0, $lines[1]['AmountDebit']);
        $this->assertSame(100.0, $lines[1]['AmountCredit']);
        $this->assertSame(0.0, $lines[2]['AmountDebit']);
        $this->assertSame(5.0, $lines[2]['AmountCredit']);
    }

    public function test_zero_vat_omits_the_vat_line()
    {
        $lines = LandlordInvoiceV2AxLineBuilder::build($this->facts([
            'vat_total' => 0.0, 'grand_total' => 100.0, 'vat_account' => null,
        ]));

        $this->assertCount(2, $lines);
    }

    public function test_common_fields_and_dimensions_match_v1_layout()
    {
        $line = LandlordInvoiceV2AxLineBuilder::build($this->facts())[0];

        $this->assertSame('J000123', $line['JournalNum']);
        $this->assertSame(LANDLORD_INV_JOURNAL_NAME, $line['JournalName']);
        $this->assertSame('2026-09-09', $line['PaymentDate']);
        $this->assertSame(CURRENCY, $line['currency']);
        $this->assertSame('LTI2600007', $line['voucher']);
        $this->assertSame('LTI2600007', $line['Invoice']);
        $this->assertSame('LTI2600007', $line['documentNo']);
        $this->assertSame('Tax Invoice LTI2600007 ACME 09/2026', $line['Description']);
        $this->assertSame('Tax Invoice LTI2600007 ACME 09/2026', $line['Remarks']);
        $this->assertSame('', $line['paymentMethod']);
        $this->assertSame('', $line['checkBookid']);
        $this->assertSame('Building', $line['dimension1']);
        $this->assertSame('B017', $line['dimension1value']);
        $this->assertSame('Division', $line['dimension2']);
        $this->assertSame('02', $line['dimension2value']);
        $this->assertSame('Employee', $line['dimension3']);
        $this->assertSame('00000', $line['dimension3value']);
        $this->assertSame('Location', $line['dimension4']);
        $this->assertSame('00', $line['dimension4value']);
        $this->assertSame('Projects', $line['dimension5']);
        $this->assertSame('00', $line['dimension5value']);
        $this->assertSame(DATA_AREA_ID, $line['DataAreaId']);
        $this->assertSame(COMPANY, $line['company']);
    }

    public function test_missing_building_code_and_division_fall_back_to_v1_defaults()
    {
        $line = LandlordInvoiceV2AxLineBuilder::build($this->facts([
            'building_code' => null, 'ax_division' => null,
        ]))[0];

        $this->assertSame('000', $line['dimension1value']);
        $this->assertSame('02', $line['dimension2value']);
    }
}
