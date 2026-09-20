<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * PDC posting from the bank's cheque-clearing sheet.
 *
 * - bank.bank_clearing_codes: the code(s) the bank's export uses for this
 *   bank ("27 - Bank Of Muscat" => 27), comma separated because Islamic
 *   windows are entered under the parent bank in PLMS (Meethaq cheques as
 *   BANK MUSCAT), so one sheet code may point at several PLMS banks and one
 *   PLMS bank may carry several codes. Seeded from a matched sample sheet.
 * - pdc_bank_sheet_uploads / _rows: audit of each upload — who, when, which
 *   file, and per sheet cheque what happened and which PDC ids were posted.
 */
class AddPdcBankSheetUpload extends Migration
{
    const SEED = [
        'BMI'   => '27,34,41', // BANK MUSCAT, incl. Meethaq / Al Izz cheques entered under it
        'BMQ'   => '34',       // Bank Muscat MEETHAQ
        'AIB'   => '41',       // ALIZ ISLAMIC BANK
        'NBO'   => '18',
        'HSBC-1'=> '10',
        'OAB'   => '2',
        'BD'    => '25,36',    // BANK DHOFAR, incl. Maisarah
        'BS'    => '30,38',    // BANK SOHAR, incl. Sohar Islamic
        'BNW'   => '33',
        'AHB'   => '31',
        'NBA'   => '17',       // NATIONAL BANK OF AD / FAB
    ];

    public function up()
    {
        Schema::table('bank', function (Blueprint $table) {
            $table->string('bank_clearing_codes', 100)->nullable()->after('bank_code');
        });
        foreach (self::SEED as $code => $codes) {
            DB::table('bank')->where('bank_code', $code)->update(['bank_clearing_codes' => $codes]);
        }

        Schema::create('pdc_bank_sheet_uploads', function (Blueprint $table) {
            $table->increments('id');
            $table->string('file_name');
            $table->date('pdc_deposit_date');
            $table->integer('bank_id');
            $table->integer('rows_total')->default(0);
            $table->integer('rows_posted')->default(0);
            $table->integer('pdcs_posted')->default(0);
            $table->integer('created_by');
            $table->timestamps();
        });

        Schema::create('pdc_bank_sheet_upload_rows', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('upload_id');
            $table->integer('sheet_row_no');
            $table->string('cheque_no', 50);
            $table->string('bank_name')->nullable();
            $table->decimal('amount', 14, 3)->default(0);
            $table->date('cheque_date')->nullable();
            $table->string('status', 30);
            $table->string('reason', 500)->nullable();
            $table->string('pdc_ids', 500)->nullable()->comment('comma separated pdc.id posted for this row');
            $table->timestamps();

            $table->index('upload_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('pdc_bank_sheet_upload_rows');
        Schema::dropIfExists('pdc_bank_sheet_uploads');
        Schema::table('bank', function (Blueprint $table) {
            $table->dropColumn('bank_clearing_codes');
        });
    }
}
