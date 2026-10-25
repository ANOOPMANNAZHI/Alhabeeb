<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

/**
 * Adds "Cheque Bounce Copy Emails" under the Settings tab of General Settings
 * (table `configuration`): comma-separated addresses that get a copy of the
 * ARE cheque-bounce email on every bounce. Read via
 * prefixData('cheque_bounce_copy_emails')->configuration_value.
 */
class AddChequeBounceCopyEmailsSetting extends Migration
{
    public function up()
    {
        $exists = DB::table('configuration')
            ->where('configuration_settings', 'cheque_bounce_copy_emails')
            ->exists();

        if (!$exists) {
            DB::table('configuration')->insert([
                'configuration_name'     => 'settings',
                'configuration_settings' => 'cheque_bounce_copy_emails',
                'configuration_value'    => 'sirajm@alhabib.om',
                'created_by'             => 1,
                'created_at'             => now(),
                'updated_at'             => now(),
            ]);
        }
    }

    public function down()
    {
        DB::table('configuration')->where('configuration_settings', 'cheque_bounce_copy_emails')->delete();
    }
}
