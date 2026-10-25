<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

/**
 * Settings tab of General Configuration: bulk sending rate. Office 365 allows
 * 30 messages/minute and 10,000 recipients/day per mailbox; bulk stays below.
 */
class AddBulkSendSettings extends Migration
{
    const ROWS = ['bulk_emails_per_minute' => '25', 'bulk_emails_per_day' => '8000'];

    public function up()
    {
        foreach (self::ROWS as $key => $value) {
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
        DB::table('configuration')->whereIn('configuration_settings', array_keys(self::ROWS))->delete();
    }
}
