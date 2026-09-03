<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDepositRefundDeductionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deposit_refund_deduction', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('deposit_refund_id');
            $table->string('deduction_reason')->comment('Cleaning, Damage, Unpaid Utility, Other');
            $table->text('description')->nullable();
            $table->float('amount');
            $table->integer('created_by');
            $table->timestamps();
        });

        Schema::table('deposit_refund_deduction', function ($table) {
            $table->foreign('deposit_refund_id')->references('id')->on('deposit_refund');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('deposit_refund_deduction');
    }
}
