<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePdcTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pdc', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('tenant_contract_id');
            $table->string('pdc_transaction_no');
            $table->date('pdc_transaction_date');
            $table->string('pdc_check_no')->unique(); 
            $table->date('pdc_check_date');   
            $table->string('pdc_reference')->unique();
            $table->date('pdc_recieve_date'); 
            $table->integer('pdc_period'); 
            $table->float('pdc_amt')->nullable();
            $table->date('pdc_deposit_date')->nullable();
            $table->integer('bank_id');   
            $table->integer('pdc_stage');
            $table->date('pdc_clear_date')->nullable();
            $table->string('pdc_receipt_no')->nullable();
            $table->date('pdc_cancel_date')->nullable();
            $table->longText('pdc_cancel_reason')->comment('1 - Bounce , 2 - Exchange , 3 - Other')->nullable();
            $table->integer('pdc_bounce_reason')->comment('1 -Insufficient Funds , 2 -Signature Missing , 3 -Signature Mismatch, 4 -Word in amount and figure differ ,5 -Stop Payment , 6 -Refer to Drawer,7 -Correction , 8 -Stale Cheque (Beyond six months), 9 - Misc')->nullable();
            $table->integer('pdc_cancel_by')->nullable(); 
            $table->longText('pdc_remark')->nullable();
            $table->integer('pdc_is_posted')->comment('0 - Not Posted, 1 - Posted')->nullable(); 
            $table->integer('pdc_posted_by')->nullable();
            $table->date('pdc_posted_date')->nullable();
            $table->integer('pdc_posted_bank_id')->nullable();
            $table->integer('pdc_type')->default(1)->comment('1 -  Rent, 2 - Deposit');
            $table->integer('created_by');
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });

        Schema::table('pdc', function($table) {
            $table->foreign('tenant_contract_id')->references('id')->on('tenant_contracts');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pdc');
    }
}
