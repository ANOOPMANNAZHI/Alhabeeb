<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDiscussionForumsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('discussion_forums', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('discussion_category_id');      
            $table->unsignedInteger('tenant_contract_id');      
            $table->string('discussion')->nullable();      
            $table->unsignedInteger('commented_by');      
            $table->timestamps();

            $table->foreign('discussion_category_id')->references('id')->on('discussion_categories');
            $table->foreign('commented_by')->references('id')->on('users');
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
        Schema::dropIfExists('discussion_forums');
    }
}
