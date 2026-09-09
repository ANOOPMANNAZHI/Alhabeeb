<?php

namespace Modules\BackOffice\Services;

use App\Setting;
use Illuminate\Support\Facades\DB;
use Modules\BackOffice\Entities\DepositRefund;
use Modules\BackOffice\Entities\DepositRefundReceipt;

/**
 * Issues the customer receipt for a deposit refund that withheld money.
 *
 * Never touches receipts_generation and never posts to AX. The receipt is a
 * snapshot: the lines and the tenant details are copied at issue time, so the
 * printed document does not change if the refund is edited afterwards.
 */
class DepositRefundReceiptIssuer
{
    const PREFIX_KEY  = 'deposit_refund_receipt_prefix';
    const PAYOUT_KEY  = 'deposit_refund_payout_accounts';

    /**
     * Issue a receipt for this refund, unless it has no deductions or already
     * has one.
     *
     * @return DepositRefundReceipt|null null when nothing was issued
     */
    public function issueFor(DepositRefund $refund, $userId = null)
    {
        if ($this->existingFor($refund)) {
            return null;
        }

        $figures = $this->figuresFor($refund);
        if (!$figures['has_deductions']) {
            return null;
        }

        $refund->loadMissing(['tenantContract.tenant', 'tenantContract.unit', 'tenantContract.building', 'receiptGeneration']);
        $contract = $refund->tenantContract;

        return DB::transaction(function () use ($refund, $figures, $contract, $userId) {
            $receipt = DepositRefundReceipt::create([
                'receipt_no'         => $this->nextNumber(),
                'receipt_date'       => $refund->deposit_refund_date ?: date('Y-m-d'),
                'deposit_refund_id'  => $refund->id,
                'tenant_name'        => optional(optional($contract)->tenant)->tenant_name,
                'tenant_code'        => optional(optional($contract)->tenant)->tenant_code,
                'building_name'      => optional(optional($contract)->building)->building_name,
                'unit_code'          => optional(optional($contract)->unit)->unit_code,
                'deposit_receipt_no' => optional($refund->receiptGeneration)->receipts_generation_receipt_no,
                'deposit_amount'     => $figures['deposit_amount'],
                'deduction_total'    => $figures['deduction_total'],
                'retained_amount'    => $figures['retained_amount'],
                'net_refund'         => $figures['net_refund'],
                'created_by'         => $userId,
            ]);

            $receipt->lines()->createMany($figures['lines']);

            $this->bumpNumber();

            return $receipt;
        });
    }

    /** The receipt already issued for this refund, if any. */
    public function existingFor(DepositRefund $refund)
    {
        return DepositRefundReceipt::where('deposit_refund_id', $refund->id)->first();
    }

    /** True when this refund withheld something, so a receipt is meaningful. */
    public function qualifies(DepositRefund $refund)
    {
        return $this->figuresFor($refund)['has_deductions'];
    }

    /** Run the refund's accounting rows through the builder. */
    public function figuresFor(DepositRefund $refund)
    {
        $rows = $refund->depositRefundDimension->map(function ($d) {
            return [
                'description'   => $d->description,
                'account_code'  => $d->account_code,
                'debit_amount'  => $d->debit_amount,
                'credit_amount' => $d->credit_amount,
            ];
        })->all();

        return DepositRefundReceiptBuilder::build($rows, $this->payoutCodes());
    }

    /** Account codes that mean "money handed back", from General Settings. */
    public function payoutCodes()
    {
        $setting = Setting::where('configuration_settings', self::PAYOUT_KEY)->first();

        return DepositRefundReceiptBuilder::payoutCodesFromSetting(
            $setting ? $setting->configuration_value : ''
        );
    }

    /** DRR<yy><00001>, following the same convention as the other prefixes. */
    private function nextNumber()
    {
        $setting = Setting::where('configuration_settings', self::PREFIX_KEY)->first();

        if (!$setting) {
            return 'DRR' . date('y') . str_pad(1, 5, '0', STR_PAD_LEFT);
        }

        $year = (int) date('y');
        if ((int) $setting->configuration_year !== $year) {
            // new year: restart the counter
            Setting::where('configuration_settings', self::PREFIX_KEY)
                ->update(['configuration_year' => $year, 'configuration_increment_value' => 1]);
            $setting->configuration_year = $year;
            $setting->configuration_increment_value = 1;
        }

        return $setting->configuration_value
             . $setting->configuration_year
             . str_pad($setting->configuration_increment_value, 5, '0', STR_PAD_LEFT);
    }

    private function bumpNumber()
    {
        $setting = Setting::where('configuration_settings', self::PREFIX_KEY)->first();
        if ($setting) {
            Setting::where('configuration_settings', self::PREFIX_KEY)
                ->update(['configuration_increment_value' => $setting->configuration_increment_value + 1]);
        }
    }
}
