<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Modules\BackOffice\Services\TenantDepositHeld;

/**
 * Which deposit receipts the company still holds for a tenancy, given each
 * receipt's live refund (if any). Pure: no models, no database.
 */
class TenantDepositHeldTest extends TestCase
{
    private function receipt($amount, $refundStatus = null, $refundNo = null)
    {
        return [
            'receipt_no'    => 'CPY2415246',
            'contract_no'   => 'TAG2401483',
            'amount'        => $amount,
            'refund_no'     => $refundNo,
            'refund_status' => $refundStatus,
        ];
    }

    public function test_no_receipts_means_no_deposit()
    {
        $r = TenantDepositHeld::summarise([]);

        $this->assertSame(0.0, $r['held']);
        $this->assertFalse($r['has_deposit']);
        $this->assertSame([], $r['receipts']);
    }

    public function test_receipt_without_refund_is_held()
    {
        $r = TenantDepositHeld::summarise([$this->receipt(190)]);

        $this->assertSame(190.0, $r['held']);
        $this->assertTrue($r['has_deposit']);
        $this->assertSame('held', $r['receipts'][0]['state']);
    }

    public function test_approved_refund_is_no_longer_held()
    {
        $r = TenantDepositHeld::summarise([$this->receipt(190, 4, 'AJV2600537')]);

        $this->assertSame(0.0, $r['held']);
        $this->assertFalse($r['has_deposit']);
        $this->assertSame('refunded', $r['receipts'][0]['state']);
        $this->assertSame('AJV2600537', $r['receipts'][0]['refund_no']);
    }

    public function test_refund_awaiting_approval_is_still_held()
    {
        foreach ([0, 1, 2, 3] as $status) {
            $r = TenantDepositHeld::summarise([$this->receipt(190, $status, 'AJV1')]);

            $this->assertSame(190.0, $r['held'], "status $status");
            $this->assertSame('refund_pending', $r['receipts'][0]['state'], "status $status");
        }
    }

    public function test_rejected_refund_is_still_held()
    {
        $r = TenantDepositHeld::summarise([$this->receipt(190, 5, 'AJV1')]);

        $this->assertSame(190.0, $r['held']);
        $this->assertSame('held', $r['receipts'][0]['state']);
    }

    public function test_sums_several_receipts_and_skips_refunded()
    {
        $r = TenantDepositHeld::summarise([
            $this->receipt('1,000.500'),
            $this->receipt(250, 4, 'AJV2'),
            $this->receipt('99.5'),
        ]);

        $this->assertSame(1100.0, $r['held']);
        $this->assertCount(3, $r['receipts']);
    }
}
