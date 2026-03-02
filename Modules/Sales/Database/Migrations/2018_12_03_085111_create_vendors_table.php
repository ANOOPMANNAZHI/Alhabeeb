<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVendorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vendors', function (Blueprint $table) {
            $table->increments('id');
            $table->string('vendor_code')->unique();
            $table->string('vendor_name');
            $table->unsignedInteger('vendor_type_id');            
            $table->longText('vendor_contact_address');
            $table->longText('vendor_secondary_address')->nullable();
            $table->string('vendor_pc');
            $table->unsignedInteger('location_id');
            $table->string('vendor_contact_no');
            $table->string('vendor_contact_person');
            $table->string('vendor_contact_email');
            $table->string('vendor_fax_no')->nullable();
            $table->string('vendor_acc_no');
            $table->integer('vendor_status');
            $table->unsignedInteger('bank_id');
            $table->integer('created_by');
            $table->integer('updated_by')->nullable();

            $table->timestamps();
        });



        Schema::table('vendors', function($table) {
            $table->foreign('vendor_type_id')->references('id')->on('vendor_types');
        });

          Schema::table('vendors', function($table) {
            $table->foreign('location_id')->references('id')->on('locations');
        });

        Schema::table('vendors', function($table) {
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
        Schema::dropIfExists('vendors');
    }
}
