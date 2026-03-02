<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTenantStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tenant_statuses', function (Blueprint $table) {
            $table->increments('id');
            $table->string('tenant_statuses_name')->unique()->comment('VIP,Legal,Blacklisted etc');
            $table->longText('tenant_statuses_desc')->nullable();
            $table->tinyInteger('tenant_statuses_status')->default(1)->comment('1-Active,0-Inactive');
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
        Schema::dropIfExists('tenant_statuses');
    }
}
