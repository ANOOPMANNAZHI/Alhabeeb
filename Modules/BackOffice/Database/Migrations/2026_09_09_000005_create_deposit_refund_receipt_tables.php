<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Customer-facing receipt issued when a deposit refund withholds money
 * (electricity, water, municipal tax, outstanding rent and so on).
 *
 * Deliberately a SEPARATE table from receipts_generation:
 *   * it must never be picked up by the AX receipt posting routine, which
 *     selects purely on date range + status and does not filter by type;
 *   * it must not affect any existing collection figure or report.
 *
 * The lines are a snapshot taken when the receipt is issued, so the printed
 * document stays fixed even if the refund's accounting rows are edited later.
 */
class CreateDepositRefundReceiptTables extends Migration
{
    public function up()
    {
        Schema::create('deposit_refund_receipt', function (Blueprint $table) {
            $table->increments('id');
            $table->string('receipt_no', 30)->unique();
            $table->date('receipt_date');
            $table->unsignedInteger('deposit_refund_id');

            // snapshot of who it was issued to, so the print never changes
            $table->string('tenant_name')->nullable();
            $table->string('tenant_code', 50)->nullable();
            $table->string('building_name')->nullable();
            $table->string('unit_code', 50)->nullable();
            $table->string('deposit_receipt_no', 50)->nullable();

            $table->double('deposit_amount')->default(0);   // deposit held (total debits)
            $table->double('deduction_total')->default(0);  // withheld from the deposit
            $table->double('retained_amount')->default(0);  // deposit balance not refunded
            $table->double('net_refund')->default(0);       // handed back to the customer

            $table->unsignedInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('deposit_refund_id')
                  ->references('id')->on('deposit_refund')
                  ->onDelete('cascade');
        });

        Schema::create('deposit_refund_receipt_line', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('deposit_refund_receipt_id');
            $table->string('description');
            $table->string('account_code', 50)->nullable();
            $table->double('amount')->default(0);
            // deduction = withheld, retained = deposit balance kept
            $table->string('line_type', 20)->default('deduction');
            $table->unsignedInteger('line_order')->default(1);
            $table->timestamps();

            $table->foreign('deposit_refund_receipt_id')
                  ->references('id')->on('deposit_refund_receipt')
                  ->onDelete('cascade');
        });

        // Receipt number series: DRR<yy><00001>
        if (!DB::table('configuration')->where('configuration_settings', 'deposit_refund_receipt_prefix')->exists()) {
            DB::table('configuration')->insert([
                'configuration_name'            => 'general',
                'configuration_value'           => 'DRR',
                'configuration_settings'        => 'deposit_refund_receipt_prefix',
                'configuration_icon'            => 'fa-file-text-o',
                'configuration_year'            => (int) date('y'),
                'configuration_increment_value' => 1,
                'created_by'                    => 1,
                'created_at'                    => now(),
                'updated_at'                    => now(),
            ]);
        }

        // Which account codes represent money handed back to the customer
        // (cash, cheque, bank). Everything else credited on a refund is a
        // deduction. Editable in General Settings -> Settings.
        if (!DB::table('configuration')->where('configuration_settings', 'deposit_refund_payout_accounts')->exists()) {
            DB::table('configuration')->insert([
                'configuration_name'     => 'settings',
                'configuration_settings' => 'deposit_refund_payout_accounts',
                'configuration_value'    => '12601,22461,22401,22301,12651',
                'created_by'             => 1,
                'created_at'             => now(),
                'updated_at'             => now(),
            ]);
        }
    }

    public function down()
    {
        Schema::dropIfExists('deposit_refund_receipt_line');
        Schema::dropIfExists('deposit_refund_receipt');

        DB::table('configuration')->whereIn('configuration_settings', [
            'deposit_refund_receipt_prefix',
            'deposit_refund_payout_accounts',
        ])->delete();
    }
}
