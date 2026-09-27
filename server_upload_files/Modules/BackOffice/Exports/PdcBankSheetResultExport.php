<?php

namespace Modules\BackOffice\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Modules\BackOffice\Services\BankSheetResultRows;

/**
 * Excel download of one bank-sheet posting result: "Receipts created" and,
 * when the run left cheques behind, "Not posted".
 */
class PdcBankSheetResultExport implements WithMultipleSheets
{
    protected $upload;
    protected $receipts;
    protected $notPosted;

    public function __construct($upload, $receipts, $notPosted)
    {
        $this->upload    = $upload;
        $this->receipts  = $receipts;
        $this->notPosted = $notPosted;
    }

    public function sheets(): array
    {
        $sheets = [
            new PdcBankSheetResultSheet('Receipts created', BankSheetResultRows::receiptsSheet($this->upload, $this->receipts)),
        ];
        if (count($this->notPosted) > 0) {
            $sheets[] = new PdcBankSheetResultSheet('Not posted', BankSheetResultRows::notPostedSheet($this->notPosted));
        }
        return $sheets;
    }
}
