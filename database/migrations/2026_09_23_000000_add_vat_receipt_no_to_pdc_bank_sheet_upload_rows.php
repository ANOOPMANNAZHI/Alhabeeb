<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** VAT General Receipt no(s) created (or found) for a bank-sheet row: "CPY2619717[,CPY…]". */
class AddVatReceiptNoToPdcBankSheetUploadRows extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('pdc_bank_sheet_upload_rows', 'vat_receipt_no')) {
            Schema::table('pdc_bank_sheet_upload_rows', function (Blueprint $table) {
                $table->string('vat_receipt_no', 100)->nullable();
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('pdc_bank_sheet_upload_rows', 'vat_receipt_no')) {
            Schema::table('pdc_bank_sheet_upload_rows', function (Blueprint $table) {
                $table->dropColumn('vat_receipt_no');
            });
        }
    }
}
