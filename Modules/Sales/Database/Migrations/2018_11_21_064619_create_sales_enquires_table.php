<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSalesEnquiresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_enquiries', function (Blueprint $table) {
            $table->increments('id');
            $table->string('sales_enquiry_no')->unique();
            $table->string('sales_enquiry_name');
            $table->string('sales_email')->nullable();
            $table->longText('sales_contact_address');
            $table->tinyInteger('sales_region')->default(1)->comment('1 -local, 2 -international');
            $table->unsignedInteger('tenant_type_id');            
            $table->integer('sales_no_of_unit');
            $table->string('sales_building_name')->nullable();
           // $table->unsignedInteger('price_range_id');
            $table->string('sales_mobile_no');
            $table->string('sales_company_name');
            $table->dateTime('sales_move_in_date');
            $table->unsignedInteger('building_type_id');
            $table->integer('sales_type')->comment('1 - tenant, 2 -landlord');
            $table->float('sales_size')->comment('Square meter');
            $table->unsignedInteger('sales_mode_id')->comment('Linked to enquiry_source table');
            $table->string('sales_referred_by');
            $table->longText('sales_note');
            $table->unsignedInteger('work_flow_process_id');
            $table->integer('created_by');
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });
        
        Schema::table('sales_enquiries', function($table) {
            $table->foreign('tenant_type_id')->references('id')->on('tenant_types');
        });
        Schema::table('sales_enquiries', function($table) {
            $table->foreign('sales_mode_id')->references('id')->on('enquiry_sources');
        });

        // Schema::table('sales_enquiries', function($table) {
        //     $table->foreign('price_range_id')->references('id')->on('price_ranges');
        // });

        Schema::table('sales_enquiries', function($table) {
            $table->foreign('building_type_id')->references('id')->on('building_types');
        });        
        
        Schema::table('sales_enquiries', function($table) {
            $table->foreign('work_flow_process_id')->references('id')->on('work_flow_processes');
        });


///////////////////////////////////////////////////////////////////////////////

        Schema::create('preferred_unit_types', function (Blueprint $table) {          
            $table->unsignedInteger('unit_type_id');         
            $table->unsignedInteger('sale_enquiry_id');

            $table->foreign('unit_type_id')
                    ->references('id')
                    ->on('unit_types')
                    ->onDelete('cascade');

            $table->foreign('sale_enquiry_id')
                    ->references('id')
                    ->on('sales_enquiries')
                    ->onDelete('cascade');
         });

//////////////////////////////////////////////////////////////////////////////////////
         
        Schema::create('preferred_price_ranges', function (Blueprint $table) {          
            $table->unsignedInteger('price_range_id');         
            $table->unsignedInteger('sale_enquiry_id');

            $table->foreign('price_range_id')
                    ->references('id')
                    ->on('price_ranges')
                    ->onDelete('cascade');

            $table->foreign('sale_enquiry_id')
                    ->references('id')
                    ->on('sales_enquiries')
                    ->onDelete('cascade');
         });

//////////////////////////////////////////////////////////////////////////////////////////
        

        Schema::create('preferred_locations', function (Blueprint $table) {          
            $table->unsignedInteger('location_id');         
            $table->unsignedInteger('sale_enquiry_id');

            $table->foreign('location_id')
                    ->references('id')
                    ->on('locations')
                    ->onDelete('cascade');

            $table->foreign('sale_enquiry_id')
                    ->references('id')
                    ->on('sales_enquiries')
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
        Schema::dropIfExists('sales_enquiries');
        Schema::dropIfExists('preferred_unit_type');
        Schema::dropIfExists('preferred_price_range');
        Schema::dropIfExists('preferred_location');
    }
}
