<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Modules\BackOffice\Emails\TemplatedMail;
use Modules\BackOffice\Entities\MessageLog;
use Modules\BackOffice\Entities\MessageTemplate;
use Modules\BackOffice\Services\MessageRecipient;
use Modules\BackOffice\Services\SystemMessenger;

class SystemMessengerTest extends TestCase
{
    use DatabaseTransactions;

    private $sms;

    protected function setUp()
    {
        parent::setUp();
        Mail::fake();
        $this->sms = [];
        $self = $this;
        SystemMessenger::$smsSender = function ($mobile, $msg) use ($self) { $self->smsSent($mobile, $msg); return true; };
        MessageTemplate::where('key', 'pdc_bounce_are')->update(['is_active' => true, 'sms_enabled' => false]);
    }

    public function smsSent($mobile, $msg) { $this->sms[] = [$mobile, $msg]; }

    protected function tearDown()
    {
        SystemMessenger::$smsSender = 'sendSms';
        parent::tearDown();
    }

    private function values()
    {
        return ['employee_name' => 'Anoop', 'tenant_name' => 'A & B', 'contract_no' => 'TAG1', 'building' => 'Rex', 'unit' => '5',
                'cheque_no' => '123', 'amount' => '250.000', 'cheque_date' => '15/09/2026', 'bounce_reason' => 'Insufficient Funds', 'remarks' => '-'];
    }

    public function test_sends_email_and_logs_it()
    {
        $r = SystemMessenger::send('pdc_bounce_are', new MessageRecipient('Anoop', 'are@example.com', '99123456', ['cc@example.com']), $this->values(), ['related' => ['pdc', 77]]);
        $this->assertSame(['email' => 'sent', 'sms' => null], $r);
        Mail::assertSent(TemplatedMail::class, function ($m) {
            return $m->hasTo('are@example.com') && $m->hasCc('cc@example.com') && $m->subject === 'Cheque bounced - A & B (123)'
                && strpos($m->bodyHtml, 'A &amp; B') !== false;
        });
        $log = MessageLog::where('template_key', 'pdc_bounce_are')->latest('id')->first();
        $this->assertSame(['email', 'are@example.com', 'sent', 'pdc', 77], [$log->channel, $log->recipient, $log->status, $log->related_type, $log->related_id]);
        $this->assertSame([], $this->sms);
    }

    public function test_switched_off_sends_nothing()
    {
        MessageTemplate::where('key', 'pdc_bounce_are')->update(['is_active' => false, 'sms_enabled' => true]);
        $r = SystemMessenger::send('pdc_bounce_are', new MessageRecipient('Anoop', 'are@example.com', '99123456'), $this->values());
        $this->assertSame(['email' => 'skipped_off', 'sms' => null], $r);
        Mail::assertNothingSent();
        $this->assertSame([], $this->sms);
    }

    public function test_no_email_sms_off_is_skipped_no_address()
    {
        $r = SystemMessenger::send('pdc_bounce_are', new MessageRecipient('Anoop', '', '99123456'), $this->values());
        $this->assertSame(['email' => 'skipped_no_address', 'sms' => null], $r);
        $this->assertSame([], $this->sms);
    }

    public function test_sms_on_sends_sms_even_with_email()
    {
        MessageTemplate::where('key', 'pdc_bounce_are')->update(['sms_enabled' => true]);
        $r = SystemMessenger::send('pdc_bounce_are', new MessageRecipient('Anoop', 'are@example.com', '99123456'), $this->values());
        $this->assertSame(['email' => 'sent', 'sms' => 'sent'], $r);
        $this->assertSame('99123456', $this->sms[0][0]);
        $this->assertContains('A & B', $this->sms[0][1]);
        $r = SystemMessenger::send('pdc_bounce_are', new MessageRecipient('Anoop', 'are@example.com', null), $this->values());
        $this->assertSame('skipped_no_mobile', $r['sms']);
    }

    public function test_mail_exception_is_logged_not_thrown()
    {
        Mail::shouldReceive('to')->andThrow(new \Exception('SMTP 554 rejected'));
        $r = SystemMessenger::send('pdc_bounce_are', new MessageRecipient('Anoop', 'are@example.com'), $this->values());
        $this->assertSame('failed', $r['email']);
        $this->assertContains('SMTP 554', MessageLog::latest('id')->first()->note);
    }

    public function test_missing_row_uses_default_wording()
    {
        DB::table('message_templates')->where('key', 'pdc_bounce_are')->delete();
        $r = SystemMessenger::send('pdc_bounce_are', new MessageRecipient('Anoop', 'are@example.com'), $this->values());
        $this->assertSame('sent', $r['email']);
        $this->assertContains('template_missing', MessageLog::latest('id')->first()->note);
    }

    public function test_templated_mail_renders_in_the_plms_layout()
    {
        // Go through Mailable::buildView() — what the mailer really uses — not the view directly.
        $mail = (new TemplatedMail('Subj', '<p>Dear <strong>Ali</strong></p>'))->build();
        $method = new \ReflectionMethod($mail, 'buildView');
        $method->setAccessible(true);
        $view = $method->invoke($mail);
        $this->assertInternalType('string', $view, 'must render the PLMS view, not raw html');
        $html = view($view, $mail->buildViewData())->render();
        $this->assertContains('logo_habib.png', $html);
        $this->assertContains('<p>Dear <strong>Ali</strong></p>', $html);
        $this->assertContains('Al Habib 2018', $html);
    }

    public function test_employee_name_falls_back_to_username_when_employee_record_missing()
    {
        $user = new \App\User(['username' => 'jdoe']); // employee() has withDefault(): an empty, unsaved Employee
        $this->assertSame('jdoe', \Modules\BackOffice\Services\MessageValues::employee($user)['employee_name']);
    }

    public function test_contract_unit_uses_the_unit_number()
    {
        $contract = \Modules\Sales\Entities\TenantContract::whereHas('unit', function ($q) { $q->whereNotNull('unit_no')->where('unit_no', '<>', ''); })->latest('id')->first();
        if (!$contract) {
            $this->markTestSkipped('No contract with a unit number');
        }
        $this->assertSame($contract->unit->unit_no, \Modules\BackOffice\Services\MessageValues::contract($contract)['unit']);
    }

    public function test_overlong_fields_are_truncated_in_the_log()
    {
        $r = SystemMessenger::send('pdc_bounce_are', new MessageRecipient(str_repeat('N', 300), 'are@example.com'), $this->values());
        $this->assertSame('sent', $r['email']);
        $this->assertSame(150, strlen(MessageLog::latest('id')->first()->recipient_name));
    }

    public function test_switched_off_sms_only_template_is_logged_as_sms()
    {
        MessageTemplate::where('key', 'pdc_due_reminder')->update(['is_active' => false]);
        $r = SystemMessenger::send('pdc_due_reminder', new MessageRecipient('Ali', null, '99001122'), ['tenant_name' => 'Ali']);
        $this->assertSame(['email' => null, 'sms' => 'skipped_off'], $r);
        $log = MessageLog::where('template_key', 'pdc_due_reminder')->latest('id')->first();
        $this->assertSame(['sms', '99001122', 'skipped_off'], [$log->channel, $log->recipient, $log->status]);
    }

    public function test_unknown_key_is_a_programming_error()
    {
        $this->expectException(\InvalidArgumentException::class);
        SystemMessenger::send('no_such_key', new MessageRecipient('x', 'x@example.com'), []);
    }
}
