<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBankTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bank', function (Blueprint $table) {
            $table->increments('id');
            $table->string('bank_code')->unique();
            $table->string('bank_name');
            $table->string('bank_branch');
            $table->string('dim1')->nullable();
            $table->string('dim2')->nullable();
            $table->string('dim3')->nullable();
            $table->string('dim4')->nullable();
            $table->string('dim5')->nullable();
            $table->longText('bank_remark')->nullable();
            $table->tinyInteger('bank_status')->default(1)->comment('1-Active,0-Inactive');
            $table->integer('created_by');
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bank');
    }
}
