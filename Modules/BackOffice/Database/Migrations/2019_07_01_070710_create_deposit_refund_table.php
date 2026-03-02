<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDepositRefundTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deposit_refund', function (Blueprint $table) {
            $table->increments('id');
            $table->string('deposit_refund_no');
            $table->date('deposit_refund_date'); 
            $table->unsignedInteger('tenant_contract_id');
            $table->unsignedInteger('receipts_generation_id');
            $table->integer('deposit_refund_payment_method')->comment('1 - Cheque, 2 - Cash'); 
            $table->integer('deposit_refund_cheque_no')->nullable(); 
            $table->string('deposit_refund_amt');
            $table->date('deposit_refund_valid_from'); 
            $table->date('deposit_refund_valid_to'); 
            $table->longText('deposit_refund_comment')->nullable();
            $table->string('ax_batch_id')->nullable();
            $table->string('ax_invoice_no')->nullable();
            $table->nullableMorphs('dim1');
            $table->nullableMorphs('dim2');
            $table->nullableMorphs('dim3');
            $table->nullableMorphs('dim4');
            $table->nullableMorphs('dim5');
            $table->integer('deposit_refund_cancelled_by')->nullable();
            $table->date('deposit_refund_cancelled_date')->nullable();
            $table->integer('deposit_refund_posted_by')->nullable();
            $table->date('deposit_refund_posted_date')->nullable();
            $table->integer('deposit_refund_status')->default(1)->comment('1 - Active,2 - Approved, 3 - Posted, 4 - UnApproved');
            $table->integer('deposit_refund_approval_status')->default(1)->comment('0 - Active(default), 1-unapproval, 2 -PendingApproval,3 -PendingUnapproval, 4 - Approved, 5 - Reject'); 
            $table->integer('created_by');
            $table->integer('updated_by')->nullable();
            $table->softDeletes();

            $table->timestamps();
        });

        Schema::table('deposit_refund', function($table) {
            $table->foreign('tenant_contract_id')->references('id')->on('tenant_contracts');
        });
        Schema::table('deposit_refund', function($table) {
            $table->foreign('receipts_generation_id')->references('id')->on('receipts_generation');
        });

        Schema::create('deposit_refund_dim', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('deposit_refund_id');
            $table->nullableMorphs('dim1');
            $table->nullableMorphs('dim2');
            $table->nullableMorphs('dim3');
            $table->nullableMorphs('dim4');
            $table->nullableMorphs('dim5');
            $table->integer('account_code')->nullable()->comment('eg) 12211');
            $table->longText('description')->nullable()->comment('eg) RENT RECEIBLE, RENTAL INCOME');
            $table->string('type')->comment('eg) REC, SALES');
            $table->float('debit_amount')->nullable();
            $table->float('credit_amount')->nullable();
            $table->unsignedInteger('ac_codes_id');
        });

        Schema::table('deposit_refund_dim', function($table) {
            $table->foreign('deposit_refund_id')->references('id')->on('deposit_refund');
        });
        Schema::table('deposit_refund_dim', function($table) {
            $table->foreign('ac_codes_id')->references('id')->on('acc_codes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('deposit_refund');
        Schema::dropIfExists('deposit_refund_dim');
    }
}
