<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLandlordInvoiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('landlord_invoice', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('landlord_contract_id');
            $table->string('landlord_invoice_voucher_no')->unique();
            $table->date('landlord_invoice_voucher_date');
            $table->float('landlord_invoice_amt')->nullable();
            $table->text('landlord_invoice_doc_type')->nullable();
            $table->string('landlord_given_invoice_no')->nullable();
            $table->text('landlord_invoice_desc');
            $table->string('ax_batch_id')->nullable();
            $table->string('ax_invoice_no')->nullable();
            $table->integer('landlord_invoice_posted_by')->nullable();
            $table->date('landlord_invoice_posted_date')->nullable();
            $table->integer('landlord_invoice_cancelled_by')->nullable();
            $table->date('landlord_invoice_cancelled_date')->nullable();
            $table->integer('landlord_invoice_status')->default(1)->comment('1 - Active,2 - Approved, 3 - Posted, 4 - UnApproved');
            $table->integer('landlord_invoice_approval_status')->default(1)->comment('0 - Active(default), 1-unapproval, 2 -PendingApproval,3 -PendingUnapproval, 4 - Approved, 5 - Reject');
            $table->integer('created_by');
            $table->integer('updated_by')->nullable();
             $table->softDeletes();

            $table->timestamps();
        });
        Schema::table('landlord_invoice', function($table) {
            $table->foreign('landlord_contract_id')->references('id')->on('landlord_contract');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('landlord_invoice');
    }
}
