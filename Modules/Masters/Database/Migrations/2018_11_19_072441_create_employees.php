<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmployees extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->increments('id');

            $table->string('employee_code')->unique();
            $table->string('employee_name');
            $table->longText('employee_contact_address');
            $table->string('employee_contact_no');
            $table->longText('employee_secondary_address')->nullable();
            $table->string('employee_secondary_no')->nullable();
            $table->dateTime('employee_dob');
            $table->integer('designation_id');
            $table->string('employee_picture')->nullable();
            $table->tinyInteger('employee_status')->default(1)->omment('1-Active,0-Inactive');
            $table->integer('created_by');
            $table->integer('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

         Schema::table('employees', function($table) {
            $table->foreign('designation_id')->references('id')->on('designation');
        });
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employees');
    }
}
