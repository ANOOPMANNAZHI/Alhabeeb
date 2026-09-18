<?php

namespace Modules\BackOffice\Services;

use App\Setting;
use Illuminate\Support\Facades\DB;
use Modules\BackOffice\Entities\TerminationDues;
use Modules\BackOffice\Services\TerminationDuesCategory as Cat;
use Modules\BackOffice\Services\TerminationDuesSettlement as S;

/**
 * Gathers, from live data, everything that settles a dues record:
 *   - approved rent receipts newer than the ones already netted into the
 *     rent outstanding (receipts_generation type 0),
 *   - credit lines of approved general receipts created after the charges
 *     were fixed at inspection (type 1, one source per dim line),
 *   - deduction lines of live deposit refunds (same rule as the deposit
 *     refund receipt: credit lines that are neither payout nor deposit).
 * Cancelled (status 2), soft-deleted and unapproved receipts are excluded,
 * so reversing a payment needs no code here.
 */
class TerminationDuesSources
{
    /** @var array category => [codes] */
    private $map;
    /** @var array payout account codes */
    private $payout;

    public function __construct()
    {
        $setting = Setting::where('configuration_settings', Cat::SETTING_KEY)->first();
        $this->map = Cat::parseMap($setting ? $setting->configuration_value : null);

        $payout = Setting::where('configuration_settings', DepositRefundReceiptIssuer::PAYOUT_KEY)->first();
        $decoded = $payout ? json_decode((string) $payout->configuration_value, true) : null;
        $this->payout = is_array($decoded) ? $decoded : ['12601', '22461', '22401', '22301', '12651'];
    }

    public function accountMap()
    {
        return $this->map;
    }

    public function forDues(TerminationDues $dues)
    {
        return array_merge(
            $this->rentReceipts($dues),
            $this->generalReceiptLines($dues),
            $this->depositDeductions($dues)
        );
    }

    private function rentReceipts(TerminationDues $dues)
    {
        $q = DB::table('receipts_generation')
            ->where('tenant_contract_id', $dues->tenant_contract_id)
            ->where('receipts_generation_type', 0)
            // 3 = approved, 6 = approved and posted to AX; both are real money
            ->whereIn('receipts_generation_approval_status', [3, 6])
            ->where('receipts_generation_status', '<>', 2)
            ->whereNull('deleted_at');
        if ($dues->rent_receipts_upto_id) {
            $q->where('id', '>', $dues->rent_receipts_upto_id);
        }
        $out = [];
        foreach ($q->orderBy('id')->get() as $r) {
            $out[] = [
                'key'          => S::key('rent_receipt', $r->id),
                'kind'         => 'rent_receipt',
                'id'           => (int) $r->id,
                'ref'          => $r->receipts_generation_receipt_no,
                'date'         => substr((string) $r->receipts_generation_receipt_date, 0, 10),
                'amount'       => (float) $r->receipts_generation_amt,
                'category'     => Cat::RENT,
                'account_code' => null,
                'description'  => 'Rent receipt' . ($r->receipts_generation_eff_from ? ' ' . date('d/m/Y', strtotime($r->receipts_generation_eff_from)) . ' - ' . date('d/m/Y', strtotime($r->receipts_generation_eff_to)) : ''),
                'url'          => url('rentReceiptGeneration/' . $r->id),
            ];
        }
        return $out;
    }

    private function generalReceiptLines(TerminationDues $dues)
    {
        $q = DB::table('receipts_generation_dim as d')
            ->join('receipts_generation as r', 'r.id', '=', 'd.receipts_generation_id')
            ->where('r.tenant_contract_id', $dues->tenant_contract_id)
            ->where('r.receipts_generation_type', 1)
            ->whereIn('r.receipts_generation_approval_status', [3, 6])
            ->where('r.receipts_generation_status', '<>', 2)
            ->whereNull('r.deleted_at')
            ->where('d.credit_amount', '>', 0);
        if ($dues->charges_fixed_at) {
            $q->where('r.created_at', '>=', $dues->charges_fixed_at);
        }
        $out = [];
        foreach ($q->select('d.id', 'd.account_code', 'd.description', 'd.credit_amount', 'r.id as receipt_id', 'r.receipts_generation_receipt_no', 'r.receipts_generation_receipt_date', 'r.receipts_generation_description')->orderBy('d.id')->get() as $l) {
            $out[] = [
                'key'          => S::key('general_receipt_line', $l->id),
                'kind'         => 'general_receipt_line',
                'id'           => (int) $l->id,
                'ref'          => $l->receipts_generation_receipt_no,
                'date'         => substr((string) $l->receipts_generation_receipt_date, 0, 10),
                'amount'       => (float) $l->credit_amount,
                'category'     => Cat::fromAccountCode($l->account_code, $this->map),
                'account_code' => $l->account_code,
                'description'  => trim((string) ($l->description ?: $l->receipts_generation_description)),
                'url'          => url('rentReceiptGeneration/' . $l->receipt_id),
            ];
        }
        return $out;
    }

    private function depositDeductions(TerminationDues $dues)
    {
        $refunds = DB::table('deposit_refund')
            ->where('tenant_contract_id', $dues->tenant_contract_id)
            ->whereNull('deleted_at')
            ->whereNull('deposit_refund_cancelled_date')
            ->get();
        $out = [];
        foreach ($refunds as $refund) {
            $dims = DB::table('deposit_refund_dim')->where('deposit_refund_id', $refund->id)->orderBy('id')->get();
            foreach ($dims as $d) {
                $credit = (float) $d->credit_amount;
                $code = trim((string) $d->account_code);
                if ($credit <= 0 || in_array($code, array_map('strval', $this->payout), true) || $code === DepositRefundReceiptBuilder::DEPOSIT_ACCOUNT) {
                    continue;
                }
                $out[] = [
                    'key'          => S::key('deposit_deduction', $d->id),
                    'kind'         => 'deposit_deduction',
                    'id'           => (int) $d->id,
                    'ref'          => $refund->deposit_refund_no,
                    'date'         => substr((string) $refund->deposit_refund_date, 0, 10),
                    'amount'       => $credit,
                    'category'     => Cat::fromAccountCode($code, $this->map),
                    'account_code' => $code,
                    'description'  => trim((string) $d->description),
                    'url'          => url('depositRefund/' . $refund->id),
                ];
            }
        }
        return $out;
    }

    /** Receipts on the contract that exist but are not yet approved (display only). */
    public function pendingForDues(TerminationDues $dues)
    {
        return DB::table('receipts_generation')
            ->where('tenant_contract_id', $dues->tenant_contract_id)
            ->whereIn('receipts_generation_type', [0, 1])
            ->where('receipts_generation_approval_status', '<>', 3)
            ->where('receipts_generation_approval_status', '<>', 6)
            ->where('receipts_generation_status', '<>', 2)
            ->whereNull('deleted_at')
            ->where('created_at', '>=', $dues->charges_fixed_at ?: $dues->terminated_at ?: '1970-01-01')
            ->orderBy('id')
            ->get(['id', 'receipts_generation_receipt_no', 'receipts_generation_receipt_date', 'receipts_generation_amt', 'receipts_generation_type']);
    }
}
