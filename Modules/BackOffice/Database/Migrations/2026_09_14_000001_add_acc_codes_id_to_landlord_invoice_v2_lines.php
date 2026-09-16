<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAccCodesIdToLandlordInvoiceV2Lines extends Migration
{
    public function up()
    {
        Schema::table('landlord_invoice_v2_lines', function (Blueprint $table) {
            $table->unsignedInteger('acc_codes_id')->nullable()->after('description');
            $table->foreign('acc_codes_id')->references('id')->on('acc_codes')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('landlord_invoice_v2_lines', function (Blueprint $table) {
            $table->dropForeign(['acc_codes_id']);
            $table->dropColumn('acc_codes_id');
        });
    }
}
