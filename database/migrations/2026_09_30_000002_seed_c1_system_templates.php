<?php

use Illuminate\Database\Migrations\Migration;
use Modules\BackOffice\Services\SystemTemplateDefaults;

/** C1 system templates (cheque bounce). Insert-only: edited rows are never overwritten. */
class SeedC1SystemTemplates extends Migration
{
    const KEYS = ['pdc_bounce_are', 'pdc_bounce_tenant'];

    public function up()
    {
        SystemTemplateDefaults::seed(self::KEYS);
    }

    public function down()
    {
        \DB::table('message_templates')->whereIn('key', self::KEYS)->delete();
    }
}
