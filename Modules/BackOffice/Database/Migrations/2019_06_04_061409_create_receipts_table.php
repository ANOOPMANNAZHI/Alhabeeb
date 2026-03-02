<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReceiptsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('receipts_generation', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('tenant_contract_id');
            $table->integer('receipts_generation_payment_method')->comment('1 - cheque, 2 - cash');
            $table->string('receipts_generation_receipt_no')->unique();
            $table->date('receipts_generation_receipt_date');
            $table->unsignedInteger('bank_id')->nullable();
            $table->float('receipts_generation_amt')->nullable();
            $table->string('receipts_generation_cheque_no')->nullable();
            $table->text('receipts_generation_description')->nullable();
            $table->text('receipts_generation_remark')->nullable();
            $table->text('receipts_generation_narration')->nullable();
            $table->integer('receipts_generation_posted_by')->nullable();
            $table->date('receipts_generation_posted_date')->nullable();
            $table->integer('receipts_generation_status')->default(0)->comment('0- InActive(default), 1 - Active , 2 - Cancel, 3 - Posted');
            $table->integer('receipts_generation_approval_status')->default(1)->comment('1 - unapproval(default), 2 -Pending, 3 - Approved, 4 - Reject');
            $table->text('receipts_generation_reason_cancel')->nullable();
            $table->integer('receipts_generation_cancel_by')->nullable();
            $table->date('receipts_generation_cancel_date');
            $table->float('dim1')->nullable();
            $table->float('dim2')->nullable();
            $table->float('dim3')->nullable();
            $table->float('dim4')->nullable();
            $table->float('dim5')->nullable();
            $table->integer('receipts_generation_type')->comment('1 - general receipt, 0 - tenant receipt, 2- deposit receipt');

            $table->integer('created_by');
            $table->integer('updated_by')->nullable();
            $table->timestamps();

        });
        Schema::table('receipts_generation', function($table) {
            $table->foreign('bank_id')->references('id')->on('bank');
        });
        /*******************************************************************/
        Schema::create('receipts_generation_dim', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('receipts_generation_id')->nullable();
            $table->float('dim1')->nullable();
            $table->float('dim2')->nullable();
            $table->float('dim3')->nullable();
            $table->float('dim4')->nullable();
            $table->float('dim5')->nullable();
            $table->integer('account_code')->nullable()->comment('eg) 12211');
            $table->text('description')->nullable()->comment('eg) RENT RECEIBLE, RENTAL INCOME');
            $table->integer('type')->nullable()->comment('eg) REC, SALES');
            $table->float('debit_amount')->nullable();
            $table->float('credit_amount')->nullable();
            $table->text('narration')->nullable()->comment('In the case of deposit receipt');
            $table->integer('ac_codes_id')->nullable();

        });
        Schema::table('receipts_generation_dim', function($table) {
            $table->foreign('receipts_generation_id')->references('id')->on('receipts_generation');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('receipts_generation');
        Schema::dropIfExists('receipts_generation_dim');
    }
}
