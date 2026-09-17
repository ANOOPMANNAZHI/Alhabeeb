<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

/**
 * Adds a "Municipal Tax Percentage" entry under the Settings tab of General
 * Settings (table `configuration`). Used by the Vacating Unit Inspection to
 * pre-fill the Municipal Tax charge; read via
 * prefixData('municipal_tax_percentage')->configuration_value. Defaults to 3.
 */
class AddMunicipalTaxPercentageSetting extends Migration
{
    public function up()
    {
        $exists = DB::table('configuration')
            ->where('configuration_settings', 'municipal_tax_percentage')
            ->exists();

        if (!$exists) {
            DB::table('configuration')->insert([
                'configuration_name'     => 'settings',
                'configuration_settings' => 'municipal_tax_percentage',
                'configuration_value'    => '3',
                'created_by'             => 1,
                'created_at'             => now(),
                'updated_at'             => now(),
            ]);
        }
    }

    public function down()
    {
        DB::table('configuration')->where('configuration_settings', 'municipal_tax_percentage')->delete();
    }
}
