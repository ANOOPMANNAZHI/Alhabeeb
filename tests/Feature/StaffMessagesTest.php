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

    protected function setUp()
    {
        parent::setUp();
        Mail::fake();
        $self = $this;
        SystemMessenger::$smsSender = function ($mobile, $msg) use ($self) { $self->sms[] = [$mobile, $msg]; return true; };
        MessageTemplate::where('kind', 'system')->update(['is_active' => true]);
        $this->actingAs(User::find(1));
    }

    protected function tearDown()
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
        $this->assertContains('Please attend ' . $checklist->work->works_code, $this->sms[0][1]);
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

    public function test_assignment_switched_off_sends_nothing()
    {
        MessageTemplate::where('key', 'complaint_assigned')->update(['is_active' => false]);
        $checklist = $this->checklist();
        StaffMessages::complaintAssigned('complaint_assigned', $checklist->complaintEnquiry, $checklist, collect([$this->staff('tech@example.com')]));
        Mail::assertNothingSent();
        $this->assertSame([], $this->sms);
    }
}
