<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateKeyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('key', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->nullable();
            $table->unsignedInteger('tenant_id')->nullable();
            $table->unsignedInteger('landlord_id')->nullable();
            $table->unsignedInteger('building_id')->nullable();
            $table->unsignedInteger('unit_id')->nullable();
            $table->tinyInteger('status')->default(1)->comment('1 - key-In-Hand, 0 - Handover');
            $table->integer('created_by');
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });
        Schema::table('key', function($table) {
            $table->foreign('user_id')->references('id')->on('users');
        });
        Schema::table('key', function($table) {
            $table->foreign('tenant_id')->references('id')->on('tenant');
        });
        Schema::table('key', function($table) {
            $table->foreign('landlord_id')->references('id')->on('vendors');
        });
        Schema::table('key', function($table) {
            $table->foreign('building_id')->references('id')->on('buildings');
        });
        Schema::table('key', function($table) {
            $table->foreign('unit_id')->references('id')->on('units');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('key');
    }
}
