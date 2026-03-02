<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExpenseHeadTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('expense_head', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('acc_codes_id');
            $table->string('expense_name');
            $table->integer('acc_code_val');
            $table->integer('created_by');
            $table->integer('updated_by')->nullable();

            $table->timestamps();
        });

        Schema::table('expense_head', function($table) {
            $table->foreign('acc_codes_id')->references('id')->on('acc_codes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('expense_head');
    }
}
