<?php

namespace Modules\BackOffice\Services;

use Illuminate\Support\Facades\DB;

/**
 * Security deposit the company still holds for a tenancy.
 *
 *   deposit receipts = receipts of type 2 that are approved (3) or posted to
 *                      AX (6), not cancelled (status 2), not soft-deleted —
 *                      the same "money really received" rule as RentOutstanding.
 *   tenancy          = every contract of the same tenant on the same unit. A
 *                      renewal does not move the deposit: it stays on the
 *                      receipt of the contract it was first paid on.
 *   refunded         = the receipt has a live (not deleted, not cancelled)
 *                      deposit refund that is approved (4). A refund still in
 *                      draft / awaiting approval leaves the money held; a
 *                      rejected one is ignored.
 *
 * summarise() is pure and unit tested; forContract() reads the database.
 * Runs on PHP < 7.4.
 */
class TenantDepositHeld
{
    const DECIMALS = 3;

    const RECEIPT_TYPE_DEPOSIT = 2;
    const COUNTED_APPROVAL     = [3, 6];
    const STATUS_CANCELLED     = 2;

    const REFUND_APPROVED = 4;
    const REFUND_REJECTED = 5;

    /**
     * @param array $receipts each: receipt_no, contract_no, amount, refund_no, refund_status
     * @return array held, has_deposit, receipts[] (input + 'state': held | refund_pending | refunded)
     */
    public static function summarise(array $receipts)
    {
        $held = 0.0;
        $out = [];
        foreach ($receipts as $r) {
            $r['amount'] = self::money($r['amount']);
            $r['state'] = self::state(isset($r['refund_status']) ? $r['refund_status'] : null);
            if ($r['state'] !== 'refunded') {
                $held += $r['amount'];
            }
            $out[] = $r;
        }
        $held = round($held, self::DECIMALS);

        return [
            'held'        => $held,
            'has_deposit' => $held > 0,
            'receipts'    => $out,
        ];
    }

    /** Deposit summary for the tenancy the contract belongs to. */
    public static function forContract($contractId)
    {
        $contract = DB::table('tenant_contracts')->where('id', $contractId)->first(['tenant_id', 'unit_id']);
        if (!$contract) {
            return self::summarise([]);
        }

        $rows = DB::table('receipts_generation as rg')
            ->join('tenant_contracts as tc', 'tc.id', '=', 'rg.tenant_contract_id')
            ->where('tc.tenant_id', $contract->tenant_id)
            ->where('tc.unit_id', $contract->unit_id)
            ->where('rg.receipts_generation_type', self::RECEIPT_TYPE_DEPOSIT)
            ->whereIn('rg.receipts_generation_approval_status', self::COUNTED_APPROVAL)
            ->where('rg.receipts_generation_status', '<>', self::STATUS_CANCELLED)
            ->whereNull('rg.deleted_at')
            ->orderBy('rg.receipts_generation_receipt_date')
            ->orderBy('rg.id')
            ->get(['rg.id', 'rg.receipts_generation_receipt_no', 'rg.receipts_generation_amt', 'tc.tenant_contract_no']);

        $refunds = self::liveRefunds($rows->pluck('id')->all());

        $receipts = [];
        foreach ($rows as $row) {
            $refund = isset($refunds[$row->id]) ? $refunds[$row->id] : null;
            $receipts[] = [
                'receipt_no'    => $row->receipts_generation_receipt_no,
                'contract_no'   => $row->tenant_contract_no,
                'amount'        => $row->receipts_generation_amt,
                'refund_no'     => $refund ? $refund->deposit_refund_no : null,
                'refund_status' => $refund ? $refund->deposit_refund_approval_status : null,
            ];
        }

        return self::summarise($receipts);
    }

    /**
     * Latest live refund per receipt id, preferring an approved one — a
     * receipt refunded once stays refunded even if a later duplicate exists.
     */
    private static function liveRefunds(array $receiptIds)
    {
        if (!$receiptIds) {
            return [];
        }
        $rows = DB::table('deposit_refund')
            ->whereIn('receipts_generation_id', $receiptIds)
            ->whereNull('deleted_at')
            ->whereNull('deposit_refund_cancelled_date')
            ->orderBy('id')
            ->get(['receipts_generation_id', 'deposit_refund_no', 'deposit_refund_approval_status']);

        $byReceipt = [];
        foreach ($rows as $r) {
            $current = isset($byReceipt[$r->receipts_generation_id]) ? $byReceipt[$r->receipts_generation_id] : null;
            if ($current && (int) $current->deposit_refund_approval_status === self::REFUND_APPROVED) {
                continue;
            }
            $byReceipt[$r->receipts_generation_id] = $r;
        }
        return $byReceipt;
    }

    private static function state($refundStatus)
    {
        if ($refundStatus === null || $refundStatus === '') {
            return 'held';
        }
        $status = (int) $refundStatus;
        if ($status === self::REFUND_APPROVED) {
            return 'refunded';
        }
        if ($status === self::REFUND_REJECTED) {
            return 'held';
        }
        return 'refund_pending';
    }

    private static function money($value)
    {
        if (is_string($value)) {
            $value = str_replace(',', '', $value);
        }
        return round((float) $value, self::DECIMALS);
    }
}
