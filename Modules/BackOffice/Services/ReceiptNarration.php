<?php

namespace Modules\BackOffice\Services;

use Carbon\Carbon;
use Modules\BackOffice\Entities\Pdc;

/**
 * Narration of a rent receipt, shared by the printed receipt and the AX posting.
 *
 * Cash (or bank transfer) taken against a cheque - the Replace / Bounce options -
 * is saved with the remark "Received cash payment for cheque no ...". The print
 * has always rebuilt its "Being" line from the contract and shown the cheque as a
 * separate comment; the AX posting sent the stored remark instead, so AX got the
 * cheque note where the rent line belongs. Both now read the text from here.
 * Runs on PHP < 7.4 in production: no arrow functions, no ?->.
 */
class ReceiptNarration
{
    public static function rentBeing($unitNo, $buildingName, $rent, $effFrom, $effTo)
    {
        $from = $effFrom ? Carbon::parse($effFrom)->format('d-m-Y') : '';
        $to   = $effTo ? Carbon::parse($effTo)->format('d-m-Y') : '';
        return 'RENT FOR UNIT NO. ' . $unitNo . ',' . $buildingName . ' FOR ' . $from . ' TO ' . $to . '@' . $rent . '/-PM';
    }

    /** Printed as-is after "Comment:" (keeps its leading space). */
    public static function cashAgainstChequeComment($bankCode, $chequeNo, $chequeDate)
    {
        return ' Cash/B.T against ' . $bankCode . $chequeNo . ' dated ' . Carbon::parse($chequeDate)->format('Y-m-d');
    }

    /**
     * Description / Remarks for the AX journal lines of a rent receipt.
     * 'remarks' replaces the lines that sent the receipt description, 'remarks_alt'
     * the line that sent the remark. Receipts not taken against a cheque get
     * exactly the values they were posted with before.
     *
     * @param array $r remark, description, is_pdc_bounce_id, unit_no, building_name, rent,
     *                 eff_from, eff_to, bank_code, cheque_no, cheque_date
     */
    public static function forAxPosting(array $r)
    {
        if (empty($r['is_pdc_bounce_id'])) {
            return [
                'description' => $r['remark'],
                'remarks'     => $r['description'],
                'remarks_alt' => $r['remark'],
            ];
        }

        $note = !empty($r['cheque_no'])
            ? trim(self::cashAgainstChequeComment($r['bank_code'], $r['cheque_no'], $r['cheque_date']))
            : $r['remark'];

        return [
            'description' => self::rentBeing($r['unit_no'], $r['building_name'], $r['rent'], $r['eff_from'], $r['eff_to']),
            'remarks'     => $note,
            'remarks_alt' => $note,
        ];
    }

    /** forAxPosting() for a ReceiptsGeneration model (loads the contract and the linked cheque). */
    public static function forAxPostingOf($receipt)
    {
        $contract = $receipt->tenantContractInfo;
        $pdc = $receipt->receipts_generation_is_pdc_bounce_id
            ? Pdc::with('bankInfo')->where('id', $receipt->receipts_generation_is_pdc_bounce_id)->first()
            : null;

        return self::forAxPosting([
            'remark'           => $receipt->receipts_generation_remark,
            'description'      => $receipt->receipts_generation_description,
            'is_pdc_bounce_id' => $receipt->receipts_generation_is_pdc_bounce_id,
            'unit_no'          => $contract && $contract->Unit ? $contract->Unit->unit_no : '',
            'building_name'    => $contract && $contract->building ? $contract->building->building_name : '',
            'rent'             => $contract ? $contract->tenant_contract_rent : '',
            'eff_from'         => $receipt->receipts_generation_eff_from,
            'eff_to'           => $receipt->receipts_generation_eff_to,
            'bank_code'        => $pdc && $pdc->bankInfo ? $pdc->bankInfo->bank_code : '',
            'cheque_no'        => $pdc ? $pdc->pdc_check_no : null,
            'cheque_date'      => $pdc ? $pdc->pdc_check_date : null,
        ]);
    }
}
