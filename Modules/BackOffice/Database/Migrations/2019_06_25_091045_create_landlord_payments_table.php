<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLandlordPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('landlord_payments', function (Blueprint $table) {
            $table->increments('id');
            $table->string('landlord_payment_no');
            $table->date('landlord_payment_date'); 
            $table->unsignedInteger('landlord_contract_id');
            $table->unsignedInteger('landlord_invoice_id');
            $table->string('landlord_payment_invoice_amt');
            $table->string('landlord_payment_balance_amt');
            $table->string('landlord_payment_amount');
            $table->integer('landlord_payment_method')->comment('0 - Cash, 1 - Cheque'); 
            $table->unsignedInteger('landlord_payment_currency_id');
            $table->unsignedInteger('bank_id');
            $table->longText('landlord_payment_comment')->nullable();
            $table->string('ax_batch_id')->nullable();
            $table->string('ax_payment_no')->nullable();
            $table->integer('landlord_payment_status')->default(1)->comment('1 - Active,2 - Approved, 3 - Posted, 4 - UnApproved');
            $table->integer('landlord_payment_approval_status')->default(1)->comment('0 - Active(default), 1-unapproval, 2 -PendingApproval,3 -PendingUnapproval, 4 - Approved, 5 - Reject');
            $table->longText('landlord_payment_reason_cancel')->nullable();
            $table->integer('landlord_payment_cancel_by')->nullable();
            $table->date('landlord_payment_cancel_date')->nullable();; 
            $table->integer('landlord_payment_posted_by')->nullable();
            $table->date('landlord_payment_posted_date')->nullable();; 
            $table->integer('created_by');
            $table->integer('updated_by')->nullable();
            $table->softDeletes();

            $table->timestamps();
        });
        Schema::table('landlord_payments', function($table) {
            $table->foreign('landlord_contract_id')->references('id')->on('landlord_contract');
        });
         Schema::table('landlord_payments', function($table) {
            $table->foreign('bank_id')->references('id')->on('bank');
        });
         Schema::table('landlord_payments', function($table) {
            $table->foreign('landlord_invoice_id')->references('id')->on('landlord_invoice');
        });
         Schema::table('landlord_payments', function($table) {
            $table->foreign('landlord_payment_currency_id')->references('id')->on('currency');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('landlord_payments');
    }
}
