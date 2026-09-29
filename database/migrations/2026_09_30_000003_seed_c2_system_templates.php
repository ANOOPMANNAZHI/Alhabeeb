<?php

use Illuminate\Database\Migrations\Migration;
use Modules\BackOffice\Services\SystemTemplateDefaults;

/** C2 system templates (tenant and landlord messages). Insert-only: edited rows are never overwritten. */
class SeedC2SystemTemplates extends Migration
{
    const KEYS = ['complaint_registered', 'complaint_completed', 'complaint_closed', 'key_accepted_tenant', 'key_accepted_landlord',
                    'renewal_form', 'inspection_details', 'termination_details', 'tenant_birthday', 'pdc_due_reminder'];

    public function up()
    {
        SystemTemplateDefaults::seed(self::KEYS);
    }

    public function down()
    {
        \DB::table('message_templates')->whereIn('key', self::KEYS)->delete();
    }
}
