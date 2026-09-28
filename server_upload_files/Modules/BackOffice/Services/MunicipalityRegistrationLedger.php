<?php

namespace Modules\BackOffice\Services;

use App\Setting;
use Carbon\Carbon;
use Modules\BackOffice\Entities\AccountCodes;
use Modules\BackOffice\Entities\GeneralLedger;
use Modules\BackOffice\Exceptions\MunicipalityLedgerException;
use Modules\BackOffice\Services\MunicipalityRegistrationCharges as Charges;

/**
 * Creates, updates and removes the General Ledger vouchers for the municipal
 * tax (tax + processing charge) and the municipality penalty of one tenant
 * contract. Vouchers already posted to AX are never changed. Finance approves
 * and posts them from the General Ledger screen. Call sync() inside a DB
 * transaction with the contract row locked. PHP < 7.4: no arrow functions.
 */
class MunicipalityRegistrationLedger
{
    const POSTED = 3;

    private $defaults = [
        'municipal_tax_percentage'       => '3',
        'municipality_processing_charge' => '1',
        'municipal_tax_account'          => '41102',
        'municipal_tax_penalty_account'  => '41103',
        'municipality_payment_account'   => '12674',
    ];

    public function settings()
    {
        return [
            'rate'            => (float) $this->setting('municipal_tax_percentage'),
            'processing'      => Charges::money($this->setting('municipality_processing_charge')),
            'tax_account'     => $this->setting('municipal_tax_account'),
            'penalty_account' => $this->setting('municipal_tax_penalty_account'),
            'payment_account' => $this->setting('municipality_payment_account'),
        ];
    }

    /**
     * Registered with the municipality before this feature went live: no stored tax
     * and no vouchers. Its tax was booked by hand, so saving it must not create vouchers.
     */
    public function isLegacy($contract)
    {
        return (int) $contract->tenant_contract_is_reg_municipality === 1
            && $contract->tenant_contract_municipality_tax_amt === null
            && !$contract->tenant_contract_municipality_gl_id
            && !$contract->tenant_contract_municipality_penalty_gl_id;
    }

    public function modalData($contract)
    {
        $s = $this->settings();
        $taxGl = $this->voucher($contract->tenant_contract_municipality_gl_id);
        $penGl = $this->voucher($contract->tenant_contract_municipality_penalty_gl_id);
        $taxPosted = $taxGl && $this->isLocked($taxGl);

        return [
            'legacy'          => $this->isLegacy($contract),
            'contract_value'  => Charges::money($contract->tenant_contract_value),
            'rate'            => $s['rate'],
            'processing'      => $taxPosted && $contract->tenant_contract_municipality_processing_amt !== null
                                    ? Charges::money($contract->tenant_contract_municipality_processing_amt) : $s['processing'],
            'tax'             => $contract->tenant_contract_municipality_tax_amt !== null
                                    ? Charges::money($contract->tenant_contract_municipality_tax_amt)
                                    : Charges::defaultTax($contract->tenant_contract_value, $s['rate']),
            'penalty'         => Charges::money($contract->tenant_contract_municipality_penalty_amt),
            'tax_voucher'     => $this->voucherInfo($taxGl),
            'penalty_voucher' => $this->voucherInfo($penGl),
            'tax_locked'      => $taxPosted,
            'penalty_locked'  => $penGl && $this->isLocked($penGl),
        ];
    }

    public function sync($contract, $taxInput, $penaltyInput, $docDate, $userId, $canApprove)
    {
        if ($this->isLegacy($contract)) {
            return [
                'legacy'         => true,
                'tax_amt'        => null,
                'processing_amt' => null,
                'penalty_amt'    => Charges::money($contract->tenant_contract_municipality_penalty_amt),
                'tax_gl_id'      => null,
                'penalty_gl_id'  => null,
            ];
        }

        $s = $this->settings();
        $tax = Charges::money($taxInput);
        $penalty = Charges::money($penaltyInput);

        $taxGl = $this->voucher($contract->tenant_contract_municipality_gl_id);
        $penGl = $this->voucher($contract->tenant_contract_municipality_penalty_gl_id);

        // ---- tax + processing ------------------------------------------
        if ($taxGl && $this->isLocked($taxGl)) {
            if (!Charges::sameAmount($tax, $contract->tenant_contract_municipality_tax_amt)) {
                throw new MunicipalityLedgerException($this->lockedMessage('tax', $taxGl));
            }
            $tax = Charges::money($contract->tenant_contract_municipality_tax_amt);
            $processing = Charges::money($contract->tenant_contract_municipality_processing_amt);
            $taxGlId = $taxGl->id;
        } else {
            $processing = $s['processing'];
            $total = round($tax + $processing, 3);
            if ($total > 0) {
                $taxGl = $this->writeVoucher($taxGl, $total, 'M.TAX', $s['tax_account'], 'municipal_tax_account',
                    $s['payment_account'], $contract, $docDate, $userId, $canApprove);
                $taxGlId = $taxGl->id;
            } else {
                if ($taxGl) {
                    $taxGl->delete();
                }
                $taxGlId = null;
            }
        }

        // ---- penalty -------------------------------------------------
        if ($penGl && $penGl->trashed() && $penalty <= 0) {
            $penGlId = null; // deleted in General Ledger and no penalty any more: just drop the link
        } elseif ($penGl && $this->isLocked($penGl)) {
            if (!Charges::sameAmount($penalty, $contract->tenant_contract_municipality_penalty_amt)) {
                throw new MunicipalityLedgerException($this->lockedMessage('penalty', $penGl));
            }
            $penalty = Charges::money($contract->tenant_contract_municipality_penalty_amt);
            $penGlId = $penGl->id;
        } elseif ($penalty > 0) {
            $penGl = $this->writeVoucher($penGl, $penalty, 'M.TAX PENALTY', $s['penalty_account'], 'municipal_tax_penalty_account',
                $s['payment_account'], $contract, $docDate, $userId, $canApprove);
            $penGlId = $penGl->id;
        } else {
            if ($penGl) {
                $penGl->delete();
            }
            $penGlId = null;
        }

        return [
            'legacy'         => false,
            'tax_amt'        => $tax,
            'processing_amt' => $processing,
            'penalty_amt'    => $penalty,
            'tax_gl_id'      => $taxGlId,
            'penalty_gl_id'  => $penGlId,
        ];
    }

    // ---- helpers -------------------------------------------------------

    private function setting($key)
    {
        $row = prefixData($key);
        return $row && $row->configuration_value !== null && $row->configuration_value !== ''
            ? $row->configuration_value : $this->defaults[$key];
    }

    /** Linked voucher, including one deleted in General Ledger (so it is never silently recreated). */
    private function voucher($id)
    {
        return $id ? GeneralLedger::withTrashed()->find($id) : null;
    }

    /** Posted to AX, or deleted by Finance in General Ledger: its amount can no longer change from here. */
    private function isLocked($gl)
    {
        return $gl->trashed() || (int) $gl->general_ledger_status === self::POSTED;
    }

    private function lockedMessage($what, $gl)
    {
        return 'Municipality ' . $what . ' voucher ' . $gl->voucher_no
            . ($gl->trashed() ? ' was deleted in General Ledger; ask Finance before changing the amount.'
                              : ' is already posted to AX; make a correcting entry in General Ledger.');
    }

    private function voucherInfo($gl)
    {
        if (!$gl) {
            return null;
        }
        $parts = explode('|', (string) $gl->general_ledger_status_name);
        return [
            'voucher_no' => $gl->voucher_no,
            'status'     => $gl->trashed() ? 'Deleted in General Ledger' : trim(isset($parts[1]) ? $parts[1] : ''),
            'posted'     => (int) $gl->general_ledger_status === self::POSTED,
        ];
    }

    private function account($value, $settingKey)
    {
        $acc = AccountCodes::where('acc_code_val', trim((string) $value))->first();
        if (!$acc) {
            throw new MunicipalityLedgerException('Account ' . $value . ' (General Settings: '
                . $settingKey . ') was not found in the chart of accounts.');
        }
        return $acc;
    }

    private function writeVoucher($gl, $amount, $prefix, $debitValue, $debitSetting, $creditValue, $contract, $docDate, $userId, $canApprove)
    {
        // Nothing changed: leave the voucher (and any edits / approval Finance made) alone.
        if ($gl && Charges::sameAmount($gl->amount, $amount)
            && $gl->doc_date && $gl->doc_date->format('Y-m-d') === Carbon::parse($docDate)->format('Y-m-d')) {
            return $gl;
        }

        $debit = $this->account($debitValue, $debitSetting);
        $credit = $this->account($creditValue, 'municipality_payment_account');

        $building = $contract->building;
        $unit = $contract->Unit;
        $desc = Charges::description($prefix, $contract->tenant_contract_no,
            $building ? $building->building_name : '', $unit ? $unit->unit_no : '');

        $header = [
            'doc_date'            => Carbon::parse($docDate)->format('Y-m-d'),
            'jv_refer_no'         => $contract->tenant_contract_no,
            'general_ledger_desc' => $desc,
            'amount'              => $amount,
        ];

        if ($gl) {
            // Amount or date changed: back to the saver's approval level, so an approved
            // voucher is approved again before anyone can post the new amount.
            $gl->update($header + [
                'updated_by'                     => $userId,
                'general_ledger_status'          => $canApprove ? 2 : 1,
                'general_ledger_approval_status' => $canApprove ? 4 : 0,
            ]);
            $gl->generalLedgerDim()->delete();
        } else {
            $gl = GeneralLedger::create($header + [
                'voucher_no'                     => $this->nextVoucherNo(),
                'general_ledger_type'            => 1,
                'bank_id'                        => null,
                'created_by'                     => $userId,
                'general_ledger_status'          => $canApprove ? 2 : 1,
                'general_ledger_approval_status' => $canApprove ? 4 : 0,
            ]);
        }

        $descByAccount = [$debit->id => $debit->acc_code_desc, $credit->id => $credit->acc_code_desc];
        foreach (Charges::voucherLines($amount, $debit->id, $credit->id, $contract->building_id, $contract->unit_id) as $line) {
            $gl->generalLedgerDim()->create($line + ['description' => $descByAccount[$line['account_id']]]);
        }
        return $gl;
    }

    /** Same format as GeneralLedgerController::generalLedgerCode(), counter row locked. */
    private function nextVoucherNo()
    {
        $row = Setting::where('configuration_settings', 'general_ledger_prefix')->lockForUpdate()->first();
        $inc = (int) $row->configuration_increment_value;
        $code = $row->configuration_value . $row->configuration_year . str_pad($inc, 5, '0', STR_PAD_LEFT);
        Setting::where('id', $row->id)->update(['configuration_increment_value' => $inc + 1]);
        return $code;
    }
}
