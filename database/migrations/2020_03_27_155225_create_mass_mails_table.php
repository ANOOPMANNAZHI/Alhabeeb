<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMassMailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mass_mails', function (Blueprint $table) {
            $table->increments('id');
            $table->string('subject');
            $table->longText('content');
            $table->integer('created_by');
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });

        Schema::create('preferred_mails', function (Blueprint $table) {   
            $table->increments('id');       
            $table->integer('mass_mail_id');      
            $table->integer('user_id'); 
            $table->tinyInteger('status')->default(0)->comment('1 Success, 0 pending,2 failed');     
            $table->tinyInteger('mail_type')->comment('1 Normal, 0 cc');     
            $table->integer('created_by');
            $table->integer('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('mass_mail_id')
                    ->references('id')
                    ->on('mass_mails')
                    ->onDelete('cascade');


         $table->foreign('user_id')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade');


    });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mass_mails');
    }
}
