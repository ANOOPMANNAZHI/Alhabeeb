<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

/**
 * Municipality registration settings (General Settings -> Settings tab).
 * The tax % already exists as municipal_tax_percentage (2026_09_16_000001).
 */
class AddMunicipalityRegistrationSettings extends Migration
{
    private $settings = [
        'municipality_processing_charge' => '1',
        'municipal_tax_account'          => '41102',
        'municipal_tax_penalty_account'  => '41103',
        'municipality_payment_account'   => '12674',
    ];

    public function up()
    {
        // The id sequence can lag behind rows inserted with explicit ids; move it past MAX(id) first.
        DB::statement("SELECT setval(pg_get_serial_sequence('configuration', 'id'), (SELECT COALESCE(MAX(id), 1) FROM configuration))");

        foreach ($this->settings as $key => $value) {
            if (!DB::table('configuration')->where('configuration_settings', $key)->exists()) {
                DB::table('configuration')->insert([
                    'configuration_name'     => 'settings',
                    'configuration_settings' => $key,
                    'configuration_value'    => $value,
                    'created_by'             => 1,
                    'created_at'             => now(),
                    'updated_at'             => now(),
                ]);
            }
        }
    }

    public function down()
    {
        DB::table('configuration')->whereIn('configuration_settings', array_keys($this->settings))->delete();
    }
}
