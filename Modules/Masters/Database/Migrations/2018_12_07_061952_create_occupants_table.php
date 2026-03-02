<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOccupantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /*Schema::create('occupants', function (Blueprint $table) {
            $table->increments('id');
            $table->string('occupant_name')->unique();
            $table->string('occupant_primary_contact_no');
            $table->string('occupant_email');
            $table->string('occupant_secondary_contact_no');
            $table->string('occupant_details');
            $table->integer('occupant_no_members');
            $table->tinyInteger('occupant_status')->default(0)->comment(' 1 - yes, 2 - No ');           
            $table->integer('created_by');            
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });*/
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        /*Schema::dropIfExists('occupants');*/
    }
}
