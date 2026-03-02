<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMaintenancePaymentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('maintenance_payment', function (Blueprint $table) {
            $table->increments('id');
            $table->string('maintenance_payment_no');
            $table->date('maintenance_payment_date'); 
            $table->unsignedInteger('vendor_id');
            $table->string('maintenance_payment_doc_no')->nullable();
            $table->unsignedInteger('bank_id');
            $table->integer('maintenance_payment_method')->comment('1 - Cash, 2 - Cheque'); 
            $table->float('maintenance_payment_amount')->nullable();
            $table->longText('maintenance_payment_comment')->nullable();
            $table->string('ax_batch_id')->nullable();
            $table->string('ax_payment_no')->nullable();
            $table->integer('maintenance_payment_status')->default(1)->comment('1 - Active,2 - Approved, 3 - Posted, 4 - UnApproved');
            $table->longText('maintenance_payment_reason_cancel')->nullable();
            $table->integer('maintenance_payment_cancel_by')->nullable();
            $table->date('maintenance_payment_cancel_date')->nullable(); 
            $table->integer('maintenance_payment_posted_by')->nullable();
            $table->date('maintenance_payment_posted_date')->nullable();
            $table->integer('maintenance_payment_approval_status')->default(1)->comment('0 - Active(default), 1-unapproval, 2 -PendingApproval,3 -PendingUnapproval, 4 - Approved, 5 - Reject');
            $table->integer('created_by');
            $table->integer('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::table('maintenance_payment', function($table) {
            $table->foreign('vendor_id')->references('id')->on('vendors');
        });
        Schema::table('maintenance_payment', function($table) {
            $table->foreign('bank_id')->references('id')->on('bank');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('maintenance_payment');
    }
}
