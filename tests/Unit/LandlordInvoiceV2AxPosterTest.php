<?php

namespace Tests\Unit;

use Tests\TestCase;
use Modules\Masters\Entities\Vendor;
use Modules\Masters\Entities\Building;
use Modules\Sales\Entities\LandlordContract;
use Modules\BackOffice\Entities\LandlordInvoiceV2;
use Modules\BackOffice\Exceptions\AxPostingException;
use Modules\BackOffice\Services\LandlordInvoiceV2AxPoster;

/** Test double: records SOAP calls instead of making them; skips the DB write. */
class RecordingPoster extends LandlordInvoiceV2AxPoster
{
    public $journal = 'J000777';
    public $lineResult = 'OK';
    public $enabled = true;
    public $expense = '12306';
    public $vat = '21500';
    public $pushed = [];
    public $persisted = null;
    public $claimResult = true;
    public $released = false;

    protected function axEnabled() { return $this->enabled; }
    protected function expenseAccount() { return $this->expense; }
    protected function vatAccount() { return $this->vat; }
    protected function openJournal() { return $this->journal; }
    protected function pushLine(array $line) { $this->pushed[] = $line; return $this->lineResult; }
    protected function persist(LandlordInvoiceV2 $invoice, $journalNum, $userId)
    {
        $this->persisted = compact('journalNum', 'userId');
    }
    protected function claim(LandlordInvoiceV2 $invoice) { return $this->claimResult; }
    protected function release(LandlordInvoiceV2 $invoice) { $this->released = true; }
}

class LandlordInvoiceV2AxPosterTest extends TestCase
{
    private function invoice(array $overrides = [])
    {
        $invoice = new LandlordInvoiceV2(array_merge([
            'invoice_type' => 'tax_invoice',
            'invoice_no'   => 'LTI2600007',
            'invoice_date' => '2026-09-09',
            'period_month' => 9,
            'period_year'  => 2026,
            'vendor_name'  => 'ACME',
            'status'       => 'active',
            'subtotal'     => 100.0,
            'vat_total'    => 5.0,
            'grand_total'  => 105.0,
        ], $overrides));

        $building = new Building(['building_code' => 'B017', 'ax_division' => '02']);
        $contract = new LandlordContract([]);
        $contract->setRelation('buildingInfo', $building);

        $invoice->setRelation('vendor', new Vendor(['vendor_code' => 'V00042']));
        $invoice->setRelation('landlordContract', $contract);

        return $invoice;
    }

    public function test_posts_three_lines_and_persists_journal_number()
    {
        $poster = new RecordingPoster;

        $result = $poster->post($this->invoice(), 7);

        $this->assertSame('J000777', $result);
        $this->assertCount(3, $poster->pushed);
        $this->assertSame('J000777', $poster->pushed[0]['JournalNum']);
        $this->assertSame('V00042', $poster->pushed[0]['vendAccount']);
        $this->assertSame('Tax Invoice LTI2600007 ACME 09/2026', $poster->pushed[0]['Description']);
        $this->assertSame(['journalNum' => 'J000777', 'userId' => 7], $poster->persisted);
        $this->assertFalse($poster->released);
    }

    public function test_refuses_when_row_already_claimed()
    {
        $poster = new RecordingPoster;
        $poster->claimResult = false;

        try {
            $poster->post($this->invoice(), 7);
            $this->fail('expected exception');
        } catch (AxPostingException $e) {
            $this->assertStringContainsString('already being posted', $e->getMessage());
            $this->assertSame([], $poster->pushed);
        }
    }

    public function test_refuses_when_ax_disabled()
    {
        $poster = new RecordingPoster;
        $poster->enabled = false;

        $this->expectException(AxPostingException::class);
        $this->expectExceptionMessage('AX integration is disabled');
        $poster->post($this->invoice(), 7);
        $this->assertSame([], $poster->pushed);
    }

    public function test_refuses_non_active_invoice()
    {
        $poster = new RecordingPoster;

        $this->expectException(AxPostingException::class);
        $this->expectExceptionMessage('Only active invoices can be posted');
        $poster->post($this->invoice(['status' => 'posted']), 7);
    }

    public function test_refuses_when_vendor_has_no_code()
    {
        $poster = new RecordingPoster;
        $invoice = $this->invoice();
        $invoice->setRelation('vendor', new Vendor(['vendor_code' => '']));

        $this->expectException(AxPostingException::class);
        $this->expectExceptionMessage('Vendor has no AX vendor code');
        $poster->post($invoice, 7);
    }

    public function test_refuses_when_expense_account_missing()
    {
        $poster = new RecordingPoster;
        $poster->expense = null;

        $this->expectException(AxPostingException::class);
        $this->expectExceptionMessage('Comprehensive_Payable_Transaction');
        $poster->post($this->invoice(), 7);
    }

    public function test_refuses_when_vat_account_missing_and_vat_present()
    {
        $poster = new RecordingPoster;
        $poster->vat = '';

        $this->expectException(AxPostingException::class);
        $this->expectExceptionMessage('landlord_invoice_v2_vat_account');
        $poster->post($this->invoice(), 7);
    }

    public function test_refuses_when_vat_account_is_zero_placeholder()
    {
        $poster = new RecordingPoster;
        $poster->vat = '0';

        $this->expectException(AxPostingException::class);
        $this->expectExceptionMessage('landlord_invoice_v2_vat_account');
        $poster->post($this->invoice(), 7);
    }

    public function test_allows_blank_vat_account_when_vat_is_zero()
    {
        $poster = new RecordingPoster;
        $poster->vat = '';

        $poster->post($this->invoice(['vat_total' => 0.0, 'grand_total' => 100.0]), 7);

        $this->assertCount(2, $poster->pushed);
    }

    public function test_header_error_aborts_before_any_line()
    {
        $poster = new RecordingPoster;
        $poster->journal = 'Error';

        try {
            $poster->post($this->invoice(), 7);
            $this->fail('expected exception');
        } catch (AxPostingException $e) {
            $this->assertSame([], $poster->pushed);
            $this->assertNull($poster->persisted);
            $this->assertTrue($poster->released);
        }
    }

    public function test_line_error_aborts_without_persisting()
    {
        $poster = new RecordingPoster;
        $poster->lineResult = 'Error';

        try {
            $poster->post($this->invoice(), 7);
            $this->fail('expected exception');
        } catch (AxPostingException $e) {
            $this->assertCount(1, $poster->pushed);
            $this->assertNull($poster->persisted);
            $this->assertTrue($poster->released);
        }
    }

    public function test_soap_fault_from_open_journal_is_wrapped_as_ax_posting_exception()
    {
        $poster = new class extends RecordingPoster {
            protected function openJournal()
            {
                throw new \RuntimeException('Could not connect to host');
            }
        };

        try {
            $poster->post($this->invoice(), 7);
            $this->fail('expected exception');
        } catch (AxPostingException $e) {
            $this->assertStringContainsString('Could not connect to host', $e->getMessage());
            $this->assertNull($poster->persisted);
        }
    }
}
