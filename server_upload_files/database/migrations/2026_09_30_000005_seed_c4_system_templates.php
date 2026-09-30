<?php

use Illuminate\Database\Migrations\Migration;
use Modules\BackOffice\Services\SystemTemplateDefaults;

/** C4 system templates (accounts and the rest). Insert-only: edited rows are never overwritten. */
class SeedC4SystemTemplates extends Migration
{
    const KEYS = ['employee_welcome', 'employee_password_reset', 'password_forgot', 'are_residence_reminder'];

    public function up()
    {
        SystemTemplateDefaults::seed(self::KEYS);
    }

    public function down()
    {
        \DB::table('message_templates')->whereIn('key', self::KEYS)->delete();
    }
}
