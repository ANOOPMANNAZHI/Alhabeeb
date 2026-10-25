<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

/** Cash Receipts report screen + validation; the receipt reports no longer offer a Payment Mode filter. */
class CashReceiptReportTest extends TestCase
{
    use DatabaseTransactions;

    public function test_screen_and_validation()
    {
        $this->actingAs(User::find(1));
        $this->get(route('showCashReceiptReport'))->assertOk()->assertSee('Cash Receipts')->assertSee('Receipt Type')
            ->assertSee('All (Rent, Deposit, General)');
        $this->post(route('cashReceiptReportPdf'), ['start_date' => '2026-09-30', 'end_date' => '2026-09-01', 'download_type' => 'pdf'])
            ->assertSessionHasErrors('end_date');
        $this->post(route('cashReceiptReportPdf'), ['start_date' => '2026-09-01', 'end_date' => '2026-09-30'])
            ->assertSessionHasErrors('download_type');
    }

    public function test_payment_mode_filter_removed()
    {
        $this->actingAs(User::find(1));
        $this->get(route('showRentReceiptReport'))->assertOk()->assertDontSee('Payment Mode');
        $this->get(route('showGeneralReceiptReport'))->assertOk()->assertDontSee('Payment Mode');
    }
}
