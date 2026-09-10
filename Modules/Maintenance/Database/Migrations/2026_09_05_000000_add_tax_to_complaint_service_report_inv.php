<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Adds tax fields to complaint_service_report_inv (the service-report item
 * lines added on the Technician Service Report screen), so total_charge can
 * be calculated inclusive of tax while preserving the base amounts and the
 * exact tax rate applied at the time (independent of later Settings changes).
 */
class AddTaxToComplaintServiceReportInv extends Migration
{
    public function up()
    {
        Schema::table('complaint_service_report_inv', function (Blueprint $table) {
            $table->decimal('tax_percentage', 8, 3)->nullable()->after('total_charge');
            $table->decimal('tax_amount', 12, 3)->nullable()->after('tax_percentage');
        });
    }

    public function down()
    {
        Schema::table('complaint_service_report_inv', function (Blueprint $table) {
            $table->dropColumn(['tax_percentage', 'tax_amount']);
        });
    }
}
