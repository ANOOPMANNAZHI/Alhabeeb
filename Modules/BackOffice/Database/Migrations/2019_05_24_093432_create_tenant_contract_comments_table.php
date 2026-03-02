<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTenantContractCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tenant_contract_comments', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('tenant_contract_id');
            $table->tinyInteger('status')->default(0)->comment('1-On Hold,2-No Maintenance,5-Blacklisted,6-Legal');
            $table->longText('comment');
            $table->integer('created_by');
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });
        Schema::table('tenant_contract_comments', function($table) {
            $table->foreign('tenant_contract_id')->references('id')->on('tenant_contracts');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tenant_contract_comments');
    }
}
