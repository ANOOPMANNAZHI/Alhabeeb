<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * AX posting support for Landlord Invoice v2:
 *  - ax_batch_id / ax_invoice_no / posted_by / posted_at on landlord_invoice_v2
 *  - a General Settings entry holding the ledger account used for the VAT line.
 * See docs/superpowers/specs/2026-09-09-landlord-invoice-v2-ax-posting-design.md
 */
class AddAxPostingToLandlordInvoiceV2 extends Migration
{
    public function up()
    {
        Schema::table('landlord_invoice_v2', function (Blueprint $table) {
            $table->string('ax_batch_id', 50)->nullable()->after('grand_total');
            $table->string('ax_invoice_no', 30)->nullable()->after('ax_batch_id');
            $table->unsignedInteger('posted_by')->nullable()->after('ax_invoice_no');
            $table->timestamp('posted_at')->nullable()->after('posted_by');
        });

        $exists = DB::table('configuration')
            ->where('configuration_settings', 'landlord_invoice_v2_vat_account')
            ->exists();

        if (!$exists) {
            DB::table('configuration')->insert([
                'configuration_name'     => 'settings',
                'configuration_settings' => 'landlord_invoice_v2_vat_account',
                'configuration_value'    => '',
                'created_by'             => 1,
                'created_at'             => now(),
                'updated_at'             => now(),
            ]);
        }
    }

    public function down()
    {
        Schema::table('landlord_invoice_v2', function (Blueprint $table) {
            $table->dropColumn(['ax_batch_id', 'ax_invoice_no', 'posted_by', 'posted_at']);
        });

        DB::table('configuration')
            ->where('configuration_settings', 'landlord_invoice_v2_vat_account')
            ->delete();
    }
}
