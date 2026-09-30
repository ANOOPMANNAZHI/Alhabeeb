<?php

use Illuminate\Database\Migrations\Migration;
use Modules\BackOffice\Services\SystemTemplateDefaults;

/** C3 system templates (staff and legal messages). Insert-only: edited rows are never overwritten. */
class SeedC3SystemTemplates extends Migration
{
    const KEYS = ['complaint_assigned', 'complaint_subassigned', 'enquiry_new', 'enquiry_assigned', 'enquiry_reassigned', 'enquiry_rejected',
                  'legal_deposit_refund_posted', 'legal_rental_income_posted', 'legal_pdc_posted', 'legal_receipt_posted', 'legal_contract_revoked',
                  'legal_case_received', 'legal_case_approved', 'legal_case_referback', 'legal_advisor_approved', 'legal_advisor_referback',
                  'legal_case_closed', 'legal_advisor_closed', 'legal_status_note'];

    public function up()
    {
        SystemTemplateDefaults::seed(self::KEYS);
    }

    public function down()
    {
        \DB::table('message_templates')->whereIn('key', self::KEYS)->delete();
    }
}
