<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInvoiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('tenant_contract_id');
            $table->integer('tenant_invoice_type')->comment('1 - Rent, 2 - Deposit');
            $table->string('tenant_invoice_no')->unique();
            $table->date('tenant_invoice_date');
            $table->float('tenant_invoice_amt')->nullable();
            $table->string('ax_batch_id')->nullable();
            $table->string('ax_invoice_no')->nullable();
            $table->date('tenant_invoice_posted_date')->nullable();
            $table->integer('tenant_invoice_posted_by')->nullable();
            $table->date('tenant_invoice_cancelled_date')->nullable();
            $table->longText('tenant_invoice_desc')->nullable();
            $table->integer('tenant_invoice_status')->default(1)->comment('1 - Active (default), 2 - Cancel, 3 - Posted');
            $table->integer('created_by');
            $table->integer('updated_by')->nullable();
            $table->timestamps();

        });

        Schema::table('invoice', function($table) {
            $table->foreign('tenant_contract_id')->references('id')->on('tenant_contracts');
        });

        Schema::create('tenant_invoice_dimensions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('invoice_id');
            $table->string('dim1')->nullable();
            $table->string('dim2')->nullable();
            $table->string('dim3')->nullable();
            $table->string('dim4')->nullable();
            $table->string('dim5')->nullable();
            $table->string('dimension_type')->nullable();
            $table->float('debit_amount')->nullable();
            $table->float('credit_amount')->nullable();
            $table->integer('ac_codes_id');  
            $table->integer('acc_code_no');          
            $table->string('acc_code_desc');
            $table->integer('created_by');
            $table->integer('updated_by')->nullable();
            $table->timestamps();

        });
        Schema::table('tenant_invoice_dimensions', function($table) {
            $table->foreign('invoice_id')->references('id')->on('invoice');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tenant_invoice_dimensions');
        Schema::dropIfExists('invoice');

    }
}
