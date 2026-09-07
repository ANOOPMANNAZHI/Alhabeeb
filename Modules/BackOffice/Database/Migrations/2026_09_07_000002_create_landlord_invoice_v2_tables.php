<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLandlordInvoiceV2Tables extends Migration
{
    public function up()
    {
        Schema::create('landlord_invoice_v2', function (Blueprint $table) {
            $table->increments('id');
            $table->string('invoice_type', 30);
            $table->string('invoice_no', 30)->unique();
            $table->date('invoice_date');
            $table->unsignedInteger('vendor_id');
            $table->unsignedInteger('landlord_contract_id');
            $table->unsignedTinyInteger('period_month');
            $table->unsignedSmallInteger('period_year');
            $table->string('vendor_name')->nullable();
            $table->string('building_name')->nullable();
            $table->text('vendor_address')->nullable();
            $table->string('vatin_no', 50)->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamp('voided_at')->nullable();
            $table->unsignedInteger('voided_by')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->double('subtotal')->default(0);
            $table->double('vat_total')->default(0);
            $table->double('grand_total')->default(0);
            $table->timestamps();
        });

        Schema::create('landlord_invoice_v2_lines', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('landlord_invoice_v2_id');
            $table->string('description');
            $table->double('amount')->default(0);
            $table->double('vat_amount')->default(0);
            $table->unsignedInteger('line_order')->default(1);
            $table->timestamps();

            $table->foreign('landlord_invoice_v2_id')
                ->references('id')->on('landlord_invoice_v2')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('landlord_invoice_v2_lines');
        Schema::dropIfExists('landlord_invoice_v2');
    }
}
