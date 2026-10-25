<?php

use Illuminate\Database\Migrations\Migration;
use Modules\BackOffice\Services\SystemTemplateDefaults;

/** New tenant cheque bounced (to the sales person) system template. Insert-only: an edited row is never overwritten. */
class SeedPdcBounceSalesTemplate extends Migration
{
    const KEYS = ['pdc_bounce_sales'];

    public function up()
    {
        SystemTemplateDefaults::seed(self::KEYS);
    }

    public function down()
    {
        \DB::table('message_templates')->whereIn('key', self::KEYS)->delete();
    }
}
