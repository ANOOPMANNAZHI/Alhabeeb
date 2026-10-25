<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Modules\BackOffice\Emails\TemplatedMail;
use Modules\BackOffice\Entities\MessageLog;
use Modules\BackOffice\Entities\MessageTemplate;
use Modules\BackOffice\Services\ComplaintMessages;
use Modules\BackOffice\Services\SystemMessenger;
use Modules\Maintenance\Entities\ComplaintEnquiry;

class ComplaintMessagesTest extends TestCase
{
    use DatabaseTransactions;

    private $sms = [];

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $self = $this;
        SystemMessenger::$smsSender = function ($mobile, $msg) use ($self) { $self->sms[] = [$mobile, $msg]; return true; };
        MessageTemplate::whereIn('key', ['complaint_registered', 'complaint_completed', 'complaint_closed'])->update(['is_active' => true, 'sms_enabled' => false]);
    }

    protected function tearDown(): void
    {
        SystemMessenger::$smsSender = 'sendSms';
        parent::tearDown();
    }

    /** A tenant complaint whose tenant gets the given emails and mobile. */
    private function complaint($contact, $personal, $mobile = '99112233')
    {
        $c = ComplaintEnquiry::whereNotNull('tenant_id')->whereHas('tenant')->latest('id')->first();
        if (!$c) {
            $this->markTestSkipped('No tenant complaint locally');
        }
        $c->tenant->update(['tenant_contact_email' => $contact, 'tenant_personal_email' => $personal, 'tenant_contact_no' => $mobile]);
        return ComplaintEnquiry::find($c->id);
    }

    public function test_registered_goes_to_contact_email_first()
    {
        $c = $this->complaint('contact@example.com', 'personal@example.com');
        $r = ComplaintMessages::registered($c);
        $this->assertSame('sent', $r['email']);
        Mail::assertSent(TemplatedMail::class, function ($m) use ($c) {
            return $m->hasTo('contact@example.com') && $m->subject === 'Complaint ' . $c->complaint_no . ' registered';
        });
        $this->assertSame(1, MessageLog::where('template_key', 'complaint_registered')->where('related_type', 'complaint')->where('related_id', $c->id)->count());
    }

    public function test_completed_goes_to_personal_email_first_and_sends_sms_when_switched_on()
    {
        $c = $this->complaint('contact@example.com', 'personal@example.com');
        MessageTemplate::where('key', 'complaint_completed')->update(['sms_enabled' => true]);
        $r = ComplaintMessages::completed($c);
        $this->assertSame(['email' => 'sent', 'sms' => 'sent'], $r);
        Mail::assertSent(TemplatedMail::class, function ($m) { return $m->hasTo('personal@example.com'); });
        $this->assertSame('99112233', $this->sms[0][0]);
        $this->assertStringContainsString($c->complaint_no, $this->sms[0][1]);
    }

    public function test_closed_falls_back_to_personal_email()
    {
        $c = $this->complaint('', 'personal@example.com');
        ComplaintMessages::closed($c);
        Mail::assertSent(TemplatedMail::class, function ($m) { return $m->hasTo('personal@example.com'); });
    }

    public function test_tenant_without_email_is_skipped()
    {
        $c = $this->complaint(null, null);
        $this->assertSame(['email' => 'skipped_no_address', 'sms' => null], ComplaintMessages::registered($c));
        Mail::assertNothingSent();
    }

    public function test_complaint_without_tenant_is_safe()
    {
        $c = new ComplaintEnquiry(['complaint_no' => 'CMPX1']);
        $r = ComplaintMessages::closed($c);
        $this->assertSame('skipped_no_address', $r['email']);
    }
}
