<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTenantTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tenant_types', function (Blueprint $table) {
            $table->increments('id');
            $table->string('tenant_types_name')->unique()->comment('Individual, Company, Bank, Ministry, GP building construction');
            $table->longText('tenant_types_desc')->nullable();
            $table->tinyInteger('tenant_types_status')->default(1)->comment('1-Active,0-Inactive');
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
        Schema::dropIfExists('tenant_types');
    }
}
