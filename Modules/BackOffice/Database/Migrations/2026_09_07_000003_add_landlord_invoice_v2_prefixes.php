<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class AddLandlordInvoiceV2Prefixes extends Migration
{
    public function up()
    {
        $year = (int) date('y');

        DB::table('configuration')->insert([
            [
                'configuration_name'             => 'general',
                'configuration_value'            => 'LTI',
                'configuration_settings'         => 'landlord_invoice_v2_tax_invoice_prefix',
                'configuration_icon'             => 'fa-file-text-o',
                'configuration_year'             => $year,
                'configuration_increment_value'  => 1,
                'created_by'                     => 1,
                'created_at'                     => now(),
                'updated_at'                     => now(),
            ],
            [
                'configuration_name'             => 'general',
                'configuration_value'            => 'LOD',
                'configuration_settings'         => 'landlord_invoice_v2_other_deductions_prefix',
                'configuration_icon'             => 'fa-file-text-o',
                'configuration_year'             => $year,
                'configuration_increment_value'  => 1,
                'created_by'                     => 1,
                'created_at'                     => now(),
                'updated_at'                     => now(),
            ],
        ]);
    }

    public function down()
    {
        DB::table('configuration')->whereIn('configuration_settings', [
            'landlord_invoice_v2_tax_invoice_prefix',
            'landlord_invoice_v2_other_deductions_prefix',
        ])->delete();
    }
}
