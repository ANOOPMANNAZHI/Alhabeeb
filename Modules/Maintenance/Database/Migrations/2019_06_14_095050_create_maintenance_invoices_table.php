<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMaintenanceInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('maintenance_invoices', function (Blueprint $table) {
            $table->increments('id');
            $table->string('maintenance_invoice_no');
            $table->date('maintenance_invoice_date');
            $table->unsignedInteger('vendor_id');
            $table->text('maintenance_invoice_desc')->nullable();
            $table->string('maintenance_invoice_refer_no');
            $table->string('maintenance_invoice_refer_amt');
            $table->integer('maintenance_invoice_payment_method')->default(1)->comment('1 - Cash,  2 - Cheque');
            $table->text('maintenance_invoice_comment')->nullable();
            $table->unsignedInteger('maintenance_invoice_posted_by')->nullable();
            $table->dateTime('maintenance_invoice_posted_date')->nullable();
            $table->unsignedInteger('maintenance_invoice_cancelled_by')->nullable();
            $table->dateTime('maintenance_invoice_cancelled_date')->nullable();
            $table->string('ax_batch_id')->nullable();
            $table->string('ax_invoice_no')->nullable();
            $table->integer('maintenance_invoice_status')->default(0)->comment('0- InActive(default), 1 - Active , 2 - Cancel, 3 - Posted');
            $table->integer('maintenance_invoice_approval_status')->default(1)->comment('1 - unapproval(default), 2 -Pending, 3 - Approved, 4 - Reject');
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            
            $table->foreign('vendor_id')->references('id')->on('vendors');
            $table->foreign('maintenance_invoice_posted_by')->references('id')->on('users');
            $table->foreign('maintenance_invoice_cancelled_by')->references('id')->on('users');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
            
        });
        
        
        
        
        Schema::create('maintenance_invoice_details', function (Blueprint $table) {
		    $table->increments('id');
		    $table->unsignedInteger('maintenance_invoice_id');
		   
		    $table->text('description')->nullable();
		    $table->unsignedInteger('building_id');
		    $table->unsignedInteger('unit_id');
		    $table->text('invoice_desc')->nullable();
		    $table->string('material_charge')->nullable();
            $table->string('labour_charge')->nullable();
		    $table->string('debit_amt')->nullable();
		    $table->string('credit_amt')->nullable();
		    $table->integer('technician_recovery')->default(0)->comment('1 - Yes ,0 - No (Default)');
		    $table->nullableMorphs('dim1able');
		    $table->nullableMorphs('dim2able');
		    $table->nullableMorphs('dim3able');
		    $table->nullableMorphs('dim4able');
		    $table->nullableMorphs('dim5able');
		    $table->unsignedInteger('ac_codes_id');
		     
		    $table->foreign('maintenance_invoice_id')->references('id')->on('maintenance_invoices');
		    $table->foreign('building_id')->references('id')->on('buildings');
		    $table->foreign('unit_id')->references('id')->on('units');
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
        Schema::dropIfExists('maintenance_invoice_details');
        Schema::dropIfExists('maintenance_invoices');
    }
}
