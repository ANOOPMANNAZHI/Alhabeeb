<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

/**
 * Adds a "Tax Percentage" entry under the Settings tab of General Settings
 * (table `configuration`), so a single configurable tax rate can be read
 * anywhere via prefixData('tax_percentage')->configuration_value.
 */
class AddTaxPercentageSetting extends Migration
{
    public function up()
    {
        $exists = DB::table('configuration')
            ->where('configuration_settings', 'tax_percentage')
            ->exists();

        if (!$exists) {
            DB::table('configuration')->insert([
                'configuration_name'     => 'settings',
                'configuration_settings' => 'tax_percentage',
                'configuration_value'    => '0',
                'created_by'             => 1,
                'created_at'             => now(),
                'updated_at'             => now(),
            ]);
        }
    }

    public function down()
    {
        DB::table('configuration')->where('configuration_settings', 'tax_percentage')->delete();
    }
}
