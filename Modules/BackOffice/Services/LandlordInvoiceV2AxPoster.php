<?php

namespace Modules\BackOffice\Services;

use App\Setting;
use Carbon\Carbon;
use Modules\BackOffice\Entities\AccountParams;
use Modules\BackOffice\Entities\LandlordInvoiceV2;
use Modules\BackOffice\Exceptions\AxPostingException;

/**
 * Posts one Landlord Invoice v2 to Microsoft Dynamics AX as an AP Invoice
 * Journal, using the same two SOAP helpers as v1 Landlord Invoice
 * (Dynamics::LandlordInvoiceRegisterAxHeaderPushData / ...LineItemPushData).
 *
 * The protected methods are deliberate seams: tests override them to avoid
 * the network and the database.
 */
class LandlordInvoiceV2AxPoster
{
    const VAT_SETTING_KEY = 'landlord_invoice_v2_vat_account';
    const SOAP_HEADER_SERVICE = 'AXLandlordInvoiceV2Header';
    const SOAP_LINE_SERVICE = 'AXLandlordInvoiceV2Line';

    /**
     * @return string AX journal number
     * @throws AxPostingException
     */
    public function post(LandlordInvoiceV2 $invoice, $userId)
    {
        $facts = $this->facts($invoice);            // validates, throws on problems

        try {
            $journalNum = $this->openJournal();
        } catch (AxPostingException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new AxPostingException('Microsoft Dynamics API unreachable or failed: ' . $e->getMessage(), 0, $e);
        }
        if ($journalNum === 'Error' || $journalNum === '' || $journalNum === null) {
            throw new AxPostingException('Microsoft Dynamics API Service Error while creating the journal header.');
        }
        $facts['journal_num'] = $journalNum;

        foreach (LandlordInvoiceV2AxLineBuilder::build($facts) as $index => $line) {
            try {
                $result = $this->pushLine($line);
            } catch (AxPostingException $e) {
                throw $e;
            } catch (\Throwable $e) {
                throw new AxPostingException('Microsoft Dynamics API unreachable or failed: ' . $e->getMessage(), 0, $e);
            }
            if ($result === 'Error') {
                throw new AxPostingException(sprintf(
                    'Microsoft Dynamics API Service Error on line %d (journal %s). Invoice left unposted.',
                    $index + 1, $journalNum
                ));
            }
        }

        $this->persist($invoice, $journalNum, $userId);

        return $journalNum;
    }

    /**
     * Gather and validate every scalar the line builder needs.
     * @throws AxPostingException
     */
    protected function facts(LandlordInvoiceV2 $invoice)
    {
        if (!$this->axEnabled()) {
            throw new AxPostingException('AX integration is disabled (AX_ENABLE_DISABLE).');
        }
        if ($invoice->status !== 'active') {
            throw new AxPostingException('Only active invoices can be posted to AX.');
        }

        $vendorCode = trim((string) optional($invoice->vendor)->vendor_code);
        if ($vendorCode === '') {
            throw new AxPostingException('Vendor has no AX vendor code.');
        }

        $expense = trim((string) $this->expenseAccount());
        if ($expense === '') {
            throw new AxPostingException('Debit account is not configured in Account Params (Comprehensive_Payable_Transaction).');
        }

        $vat = trim((string) $this->vatAccount());
        if ((float) $invoice->vat_total > 0 && $vat === '') {
            throw new AxPostingException('VAT ledger account is not configured (General Settings: landlord_invoice_v2_vat_account).');
        }

        $building = optional($invoice->landlordContract)->buildingInfo;

        return [
            'journal_num'     => '',
            'invoice_no'      => $invoice->invoice_no,
            'invoice_date'    => Carbon::parse($invoice->invoice_date)->format('Y-m-d'),
            'invoice_type'    => $invoice->invoice_type,
            'description'     => sprintf('%s %s %s %02d/%d',
                                    $invoice->invoice_type_label,
                                    $invoice->invoice_no,
                                    $invoice->vendor_name,
                                    (int) $invoice->period_month,
                                    (int) $invoice->period_year),
            'vendor_code'     => $vendorCode,
            'expense_account' => $expense,
            'vat_account'     => $vat,
            'building_code'   => $building ? $building->building_code : null,
            'ax_division'     => $building ? $building->ax_division : null,
            'subtotal'        => (float) $invoice->subtotal,
            'vat_total'       => (float) $invoice->vat_total,
            'grand_total'     => (float) $invoice->grand_total,
        ];
    }

    // ---- seams -----------------------------------------------------------

    protected function axEnabled()
    {
        return defined('AX_ENABLE_DISABLE') && AX_ENABLE_DISABLE == 1;
    }

    protected function expenseAccount()
    {
        $params = AccountParams::where('acc_params_tran_desc', '=', AX_VENDOR_COMP_ACC_PARAM)->first();
        return $params ? $params->acc_params_dr_acc : null;
    }

    protected function vatAccount()
    {
        $setting = Setting::where('configuration_settings', self::VAT_SETTING_KEY)->first();
        return $setting ? $setting->configuration_value : null;
    }

    protected function openJournal()
    {
        return \Dynamics::LandlordInvoiceRegisterAxHeaderPushData(self::SOAP_HEADER_SERVICE);
    }

    protected function pushLine(array $line)
    {
        return \Dynamics::LandlordInvoiceRegisterAxLineItemPushData(self::SOAP_LINE_SERVICE, [$line]);
    }

    protected function persist(LandlordInvoiceV2 $invoice, $journalNum, $userId)
    {
        $invoice->update([
            'status'        => 'posted',
            'ax_batch_id'   => $journalNum,
            'ax_invoice_no' => $invoice->invoice_no,
            'posted_by'     => $userId,
            'posted_at'     => now(),
        ]);
    }
}
