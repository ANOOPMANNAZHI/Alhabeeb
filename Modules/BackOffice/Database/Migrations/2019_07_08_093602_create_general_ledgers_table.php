<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGeneralLedgersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('general_ledgers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('voucher_no');
            $table->integer('general_ledger_type')->comment(' 1- General Ledger, 2 -Bank Payment , 3- Landlord Invoice, 4 - Bank Receipt');
            $table->string('jv_refer_no');
            $table->date('doc_date');
            $table->string('jv_amount');
            $table->text('general_ledger_desc')->nullable();
            $table->unsignedInteger('general_ledger_cancelled_by')->nullable();
            $table->timestamp('general_ledger_cancelled_date')->nullable();
            $table->unsignedInteger('general_ledger_posted_by')->nullable();
            $table->timestamp('general_ledger_posted_date')->nullable();
            $table->integer('general_ledger_status')->default(1)->comment('0 -  InActive, 1 - Active, 2 - Cancel, 3 - Posted');
            $table->integer('general_ledger_approval_status')->default(0)->comment('-- 1 -  Unapproval, 2 - Pending Approval , 3 - Pending  Unapproval, 4 - Approved , 5- Rejected');
            $table->unsignedInteger('bank_id');
            $table->string('amount');
            $table->string('ax_batch_id')->nullable();
            $table->string('ax_batch_no')->nullable();
            $table->string('agreement_no')->nullable();
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('updated_by')->nullable();
            $table->timestamps();

             $table->foreign('bank_id')->references('id')->on('bank');
             $table->foreign('general_ledger_cancelled_by')->references('id')->on('users');
             $table->foreign('general_ledger_posted_by')->references('id')->on('users');
             $table->foreign('created_by')->references('id')->on('users');
             $table->foreign('updated_by')->references('id')->on('users');
        });

        Schema::create('general_ledger_dim', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('general_ledger_id');
                $table->unsignedInteger('account_id');
                $table->text('description')->nullable();
                $table->unsignedInteger('building_id');
                $table->unsignedInteger('unit_id');
                $table->text('jv_desc')->nullable();
                $table->string('debit_amt')->nullable();
                $table->string('credit_amt')->nullable();
                $table->integer('recovery')->nullable();
                $table->nullableMorphs('dim1able');
                $table->nullableMorphs('dim2able');
                $table->nullableMorphs('dim3able');
                $table->nullableMorphs('dim4able');
                $table->nullableMorphs('dim5able');

                $table->foreign('general_ledger_id')->references('id')->on('general_ledgers');

            });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('general_ledger_dim');
        Schema::dropIfExists('general_ledgers');
    }
}
