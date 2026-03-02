<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLandlordInvoicesDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('landlord_invoice_dimensions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('landlord_invoice_id');
            $table->nullableMorphs('dim1able');
            $table->nullableMorphs('dim2able');
            $table->nullableMorphs('dim3able');
            $table->nullableMorphs('dim4able');
            $table->nullableMorphs('dim5able');
            $table->unsignedInteger('ac_codes_id');
            $table->text('description');
            $table->string('type');
            $table->string('debit_amount');
            $table->string('credit_amount');   

            $table->foreign('landlord_invoice_id')->references('id')->on('landlord_invoice');
        });

        Schema::create('landlord_invioce_distribution_break_up', function (Blueprint $table) {
             $table->increments('id');
             $table->unsignedInteger('landlord_invoice_id');
             $table->string('account_code');
             $table->string('debit_amount');
             $table->string('credit_amount');
             $table->date('date');   

             $table->foreign('landlord_invoice_id')->references('id')->on('landlord_invoice');         
        });


        Schema::create('approve_unapprove_request_history', function (Blueprint $table) {
              $table->increments('id');
              $table->nullableMorphs('requestable'); 
              $table->text('request_desc')->nullable();
              $table->integer('action')->default(1)->comment('1 - Unapproved (default),2- Pending, 3- Approval & 4 - Reject');
              $table->unsignedInteger('created_by'); 
              $table->unsignedInteger('updated_by')->nullable();
              $table->timestamps();

             $table->foreign('created_by')->references('id')->on('users');
             $table->foreign('updated_by')->references('id')->on('users');           

          });
          
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('landlord_invoice_dimensions');
        Schema::dropIfExists('landlord_invioce_distribution_break_up');
    }
}
