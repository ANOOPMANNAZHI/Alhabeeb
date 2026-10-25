<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Modules\BackOffice\Emails\TemplatedMail;
use Modules\BackOffice\Entities\MessageLog;
use Modules\BackOffice\Entities\MessageTemplate;
use Modules\BackOffice\Services\StaffMessages;
use Modules\BackOffice\Services\SystemMessenger;
use Modules\Maintenance\Entities\ComplaintChecklist;

class StaffMessagesTest extends TestCase
{
    use DatabaseTransactions;

    public $sms = [];

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $self = $this;
        SystemMessenger::$smsSender = function ($mobile, $msg) use ($self) { $self->sms[] = [$mobile, $msg]; return true; };
        MessageTemplate::where('kind', 'system')->update(['is_active' => true]);
        $this->actingAs(User::find(1));
    }

    protected function tearDown(): void
    {
        SystemMessenger::$smsSender = 'sendSms';
        parent::tearDown();
    }

    /** A staff user with an employee record; email/mobile set as given. */
    protected function staff($email, $mobile = '98765432', $skip = 0)
    {
        $user = User::whereHas('employee')->orderBy('id')->skip($skip)->first();
        $user->update(['email' => $email]);
        $user->employee->update(['employee_contact_no' => $mobile]);
        return User::find($user->id);
    }

    // --- complaint assigned / sub-assigned ---

    private function checklist()
    {
        $c = ComplaintChecklist::whereNotNull('work_id')->whereHas('complaintEnquiry')->latest('id')->first();
        if (!$c) {
            $this->markTestSkipped('No complaint ticket');
        }
        return $c;
    }

    public function test_assigned_technician_gets_email_and_sms()
    {
        $checklist = $this->checklist();
        $tech = $this->staff('tech@example.com');
        StaffMessages::complaintAssigned('complaint_assigned', $checklist->complaintEnquiry, $checklist, collect([$tech, $tech]));
        Mail::assertSent(TemplatedMail::class, 1);
        Mail::assertSent(TemplatedMail::class, function ($m) use ($checklist) {
            return $m->hasTo('tech@example.com') && strpos($m->subject, $checklist->complaintEnquiry->complaint_no) !== false;
        });
        $this->assertCount(1, $this->sms);
        $this->assertSame('98765432', $this->sms[0][0]);
        $this->assertStringContainsString('Please attend ' . $checklist->work->works_code, $this->sms[0][1]);
    }

    public function test_technician_without_email_still_gets_the_sms()
    {
        $checklist = $this->checklist();
        $tech = $this->staff('');
        StaffMessages::complaintAssigned('complaint_subassigned', $checklist->complaintEnquiry, $checklist, collect([$tech]));
        Mail::assertNothingSent();
        $this->assertCount(1, $this->sms);
        $this->assertSame(1, MessageLog::where('template_key', 'complaint_subassigned')->where('status', 'skipped_no_address')->count());
    }

    // --- enquiries ---

    private function enquiry()
    {
        $e = \Modules\Sales\Entities\SalesEnquiry::latest('id')->first();
        if (!$e) {
            $this->markTestSkipped('No enquiry');
        }
        $e->update(['sales_email' => 'customer@example.com', 'sales_enquiry_name' => 'Salim Al Hinai']);
        return \Modules\Sales\Entities\SalesEnquiry::find($e->id);
    }

    public function test_new_enquiry_goes_to_the_customer()
    {
        $e = $this->enquiry();
        $this->assertSame('sent', StaffMessages::enquiryNew($e)['email']);
        Mail::assertSent(TemplatedMail::class, function ($m) use ($e) {
            return $m->hasTo('customer@example.com') && strpos($m->subject, $e->sales_enquiry_no) !== false && strpos($m->bodyHtml, 'Dear Salim Al Hinai') !== false;
        });
    }

    public function test_reassign_reaches_every_user_and_empty_list_is_safe()
    {
        $e = $this->enquiry();
        $a = $this->staff('a@example.com', '91111111', 0);
        $b = $this->staff('b@example.com', '92222222', 1);
        StaffMessages::enquiryToStaff('enquiry_reassigned', $e, collect([$a, $b]));
        Mail::assertSent(TemplatedMail::class, 2);
        StaffMessages::enquiryToStaff('enquiry_reassigned', $e, collect());
        Mail::assertSent(TemplatedMail::class, 2);
    }

    public function test_rejected_greets_the_employee_by_name()
    {
        $e = $this->enquiry();
        $u = $this->staff('a@example.com');
        StaffMessages::enquiryToStaff('enquiry_rejected', $e, [$u]);
        $name = e($u->employee->employee_name);
        Mail::assertSent(TemplatedMail::class, function ($m) use ($name) { return strpos($m->bodyHtml, 'Dear ' . $name) !== false; });
    }

    // --- legal alerts ---

    public function test_legal_alert_one_message_per_distinct_user_with_contract_values()
    {
        $contract = \Modules\Sales\Entities\TenantContract::whereHas('tenant')->whereHas('building')->latest('id')->first();
        $a = $this->staff('a@example.com', '91111111', 0);
        $b = $this->staff('b@example.com', '92222222', 1);
        \Modules\BackOffice\Services\LegalMessages::toUsers('legal_rental_income_posted', [$a, $b, $a], $contract, ['invoice_no' => 'INV-77']);
        Mail::assertSent(TemplatedMail::class, 2);
        Mail::assertSent(TemplatedMail::class, function ($m) use ($contract) {
            return strpos($m->subject, $contract->tenant_contract_no) !== false && strpos($m->bodyHtml, 'INV-77') !== false;
        });
        $this->assertSame(2, MessageLog::where('template_key', 'legal_rental_income_posted')->where('related_type', 'contract')->where('related_id', $contract->id)->count());
    }

    public function test_legal_alert_for_users_by_role_dedupes_across_roles()
    {
        $contract = \Modules\Sales\Entities\TenantContract::whereHas('tenant')->latest('id')->first();
        $n = \App\User::role(['are', 'backoffice_manager', 'legal_advisor'])->get()->unique('id')->count();
        \Modules\BackOffice\Services\LegalMessages::toRoles('legal_deposit_refund_posted', ['are', 'backoffice_manager', 'legal_advisor'], $contract);
        $this->assertSame($n, MessageLog::where('template_key', 'legal_deposit_refund_posted')->count());
    }

    public function test_switched_off_legal_alert_is_skipped()
    {
        $contract = \Modules\Sales\Entities\TenantContract::whereHas('tenant')->latest('id')->first();
        MessageTemplate::where('key', 'legal_case_referback')->update(['is_active' => false]);
        \Modules\BackOffice\Services\LegalMessages::toUsers('legal_case_referback', [$this->staff('a@example.com')], $contract);
        Mail::assertNothingSent();
        $this->assertSame('skipped_off', MessageLog::where('template_key', 'legal_case_referback')->value('status'));
    }

    public function test_assignment_switched_off_sends_nothing()
    {
        MessageTemplate::where('key', 'complaint_assigned')->update(['is_active' => false]);
        $checklist = $this->checklist();
        StaffMessages::complaintAssigned('complaint_assigned', $checklist->complaintEnquiry, $checklist, collect([$this->staff('tech@example.com')]));
        Mail::assertNothingSent();
        $this->assertSame([], $this->sms);
    }
}
