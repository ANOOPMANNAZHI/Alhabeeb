<?php

namespace Modules\BackOffice\Services;

use Carbon\Carbon;
use Modules\BackOffice\Entities\AccountCodes;
use Modules\BackOffice\Entities\Pdc;
use Modules\BackOffice\Entities\ReceiptGenerationDim;
use Modules\BackOffice\Entities\ReceiptsGeneration;
use Modules\BackOffice\Http\Controllers\RoutinesController;

/**
 * The VAT part of a rent + VAT cheque, booked as a General Receipt in the
 * format the back office types by hand (reference: CPY2619717):
 * "VAT for 16.08.26 to 15.09.26 UNIT NO. S 03 , Teeb Complex", one credit
 * line to 12211 RENT RECEIVABLE. Runs inside the caller's transaction.
 * PHP < 7.4 in production: no arrow functions.
 */
class VatReceiptCreator
{
    const SEQUENCE       = 'receipt_cheque';
    const LEDGER_ACCOUNT = 12211;

    private $routines;

    public function __construct(RoutinesController $routines)
    {
        $this->routines = $routines;
    }

    public static function remark($effFrom, $effTo, $unitNo, $buildingName)
    {
        return self::narration($effFrom, $effTo) . ' UNIT NO. ' . $unitNo . ' , ' . $buildingName;
    }

    public static function narration($effFrom, $effTo)
    {
        return 'VAT for ' . Carbon::parse($effFrom)->format('d.m.y') . ' to ' . Carbon::parse($effTo)->format('d.m.y');
    }

    public static function chequeNo($bankCode, $chequeNo)
    {
        return trim((string) $bankCode) . '__' . trim((string) $chequeNo);
    }

    public static function division($axDivision)
    {
        return trim((string) $axDivision) === '01' ? AX_DIVISION_HO : AX_DIVISION_PLMS;
    }

    /** A live (not deleted, not cancelled) General Receipt already booked for this agreement's cheque. */
    public function existing($contractId, $chequeNo)
    {
        return ReceiptsGeneration::where('tenant_contract_id', $contractId)
            ->where('receipts_generation_type', 1)
            ->where('receipts_generation_status', '<>', 2)
            ->whereRaw("split_part(receipts_generation_cheque_no, '__', 2) = ?", [trim((string) $chequeNo)])
            ->orderBy('id')
            ->first();
    }

    /** @return array ['receipt_no' => string, 'created' => bool] */
    public function create(array $a)
    {
        $contract = $a['contract'];
        $vatPdcs  = isset($a['vat_pdcs']) ? $a['vat_pdcs'] : [];

        $existing = $this->existing($contract->id, $a['cheque_no']);
        if ($existing !== null) {
            $this->closePdcs($vatPdcs, $existing->receipts_generation_receipt_no, $a['deposit_date']);
            return ['receipt_no' => $existing->receipts_generation_receipt_no, 'created' => false];
        }

        $account = AccountCodes::where('acc_code_val', self::LEDGER_ACCOUNT)->first();
        if ($account === null) {
            throw new \RuntimeException('Account ' . self::LEDGER_ACCOUNT . ' (RENT RECEIVABLE) is missing');
        }

        $approved  = \Auth::user()->hasPermissionTo('request_approval_receipt');
        $receiptNo = $this->routines->receiptGenerateCode(self::SEQUENCE);
        $remark    = self::remark($a['eff_from'], $a['eff_to'], $contract->Unit->unit_no, $contract->building->building_name);
        $amount    = round((float) $a['amount'], 3);

        $receipt = ReceiptsGeneration::create([
            'tenant_contract_id'                  => $contract->id,
            'receipts_generation_payment_method'  => 1, // cheque
            'receipts_generation_receipt_no'      => $receiptNo,
            'receipts_generation_receipt_date'    => $a['deposit_date'],
            'bank_id'                             => $a['deposit_bank_id'],
            'receipts_generation_amt'             => $amount,
            'receipts_generation_cheque_no'       => self::chequeNo($a['bank_code'], $a['cheque_no']),
            'receipts_generation_remark'          => $remark,
            'receipts_generation_description'     => $remark,
            'receipts_generation_posted_by'       => \Auth::user()->id,
            'receipts_generation_posted_date'     => date('Y-m-d'),
            'receipts_generation_status'          => $approved ? 1 : 0,
            'receipts_generation_approval_status' => $approved ? 3 : 1,
            'receipts_generation_type'            => 1, // general
            'receipts_generation_index'           => 0,
            'finance_dim'                         => self::division($contract->building->ax_division),
            'created_by'                          => \Auth::user()->id,
        ]);

        ReceiptGenerationDim::create([
            'receipts_generation_id' => $receipt->id,
            'account_code'           => self::LEDGER_ACCOUNT,
            'description'            => $account->acc_code_desc,
            'dim_type'               => 'LEDGER',
            'debit_amount'           => 0,
            'credit_amount'          => $amount,
            'narration'              => self::narration($a['eff_from'], $a['eff_to']),
            'ac_codes_id'            => $account->id,
            'created_by'             => \Auth::user()->id,
        ]);

        $this->routines->incrementSequenceNo(self::SEQUENCE);
        $this->closePdcs($vatPdcs, $receiptNo, $a['deposit_date']);

        return ['receipt_no' => $receiptNo, 'created' => true];
    }

    private function closePdcs(array $vatPdcs, $receiptNo, $depositDate)
    {
        foreach ($vatPdcs as $pdc) {
            Pdc::where('id', $pdc->id)->update([
                'pdc_is_posted'    => 1,
                'pdc_deposit_date' => $depositDate,
                'pdc_posted_date'  => date('Y-m-d'),
                'pdc_posted_by'    => \Auth::user()->id,
                'pdc_receipt_no'   => $receiptNo,
            ]);
        }
    }
}
