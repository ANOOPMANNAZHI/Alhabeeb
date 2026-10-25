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

    public function test_secret_is_hidden_in_the_note_of_a_failed_send()
    {
        $secret = 'https://plms.test/password/reset/zz999?email=a%40b.om';
        Mail::shouldReceive('to')->andThrow(new \Exception('SMTP refused ' . $secret));
        $r = SystemMessenger::send('pdc_bounce_are', new MessageRecipient('Anoop', 'are@example.com'), $this->values(), ['secret' => [$secret]]);
        $this->assertSame('failed', $r['email']);
        $note = (string) MessageLog::latest('id')->first()->note;
        $this->assertContains('[link hidden]', $note);
        $this->assertNotContains('zz999', $note);
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

    /** C3 review: sends run inside posting transactions; a failed log insert must not abort them (PostgreSQL). */
    public function test_failed_log_insert_does_not_abort_the_callers_transaction()
    {
        // related_id is an integer column: a non-numeric value makes the log INSERT fail
        SystemMessenger::send('pdc_bounce_are', new MessageRecipient('Anoop', 'are@example.com'), $this->values(), ['related' => ['pdc', 'not-a-number']]);
        $this->assertSame(1, (int) DB::selectOne('select 1 as one')->one, 'transaction still usable');
    }

    public function test_secret_values_are_hidden_in_the_log_but_sent()
    {
        MessageTemplate::where('key', 'pdc_bounce_are')->update(['is_active' => true, 'sms_enabled' => true,
            'email_body' => '<p>Link: <a href="{remarks}">go</a> {remarks}</p>', 'sms_body' => 'Link {remarks}']);
        $secret = 'https://plms.test/password/reset/abc123?email=a%40b.om&x=1';
        $values = ['remarks' => $secret] + $this->values();
        SystemMessenger::send('pdc_bounce_are', new MessageRecipient('Anoop', 'are@example.com', '99123456'), $values, ['secret' => [$secret]]);

        Mail::assertSent(TemplatedMail::class, function ($m) {
            return strpos($m->bodyHtml, 'abc123') !== false;
        });
        $this->assertContains('abc123', $this->sms[0][1]);
        $rows = MessageLog::where('template_key', 'pdc_bounce_are')->latest('id')->take(2)->get();
        $this->assertCount(2, $rows);
        foreach ($rows as $row) {
            $this->assertNotContains('abc123', (string) $row->body, $row->channel);
            $this->assertContains('[link hidden]', (string) $row->body, $row->channel);
        }
    }

    public function test_unknown_key_is_a_programming_error()
    {
        $this->expectException(\InvalidArgumentException::class);
        SystemMessenger::send('no_such_key', new MessageRecipient('x', 'x@example.com'), []);
    }

    public function test_send_texts_uses_the_chosen_channels()
    {
        $texts = ['email_subject' => 'Notice for {tenant_name}', 'email_body' => '<p>Dear {tenant_name}</p>', 'sms_body' => 'Hi {tenant_name}'];
        $to = new MessageRecipient('Ali', 'ali@example.com', '99111222');

        $r = SystemMessenger::sendTexts('bulk:one-off', $texts, $to, ['tenant_name' => 'Ali'], ['related' => ['bulk_send', 7], 'channels' => ['email' => true, 'sms' => false]]);
        $this->assertSame(['email' => 'sent', 'sms' => null], $r);
        Mail::assertSent(TemplatedMail::class, function ($m) {
            return $m->hasTo('ali@example.com') && $m->subject === 'Notice for Ali' && strpos($m->bodyHtml, 'Dear Ali') !== false;
        });
        $this->assertSame([], $this->sms);
        $log = MessageLog::where('template_key', 'bulk:one-off')->latest('id')->first();
        $this->assertSame(['email', 'sent', 'bulk_send', 7], [$log->channel, $log->status, $log->related_type, (int) $log->related_id]);

        $r = SystemMessenger::sendTexts('bulk:one-off', $texts, $to, ['tenant_name' => 'Ali'], ['channels' => ['email' => false, 'sms' => true]]);
        $this->assertSame(['email' => null, 'sms' => 'sent'], $r);
        $this->assertSame([['99111222', 'Hi Ali']], $this->sms);
    }

    public function test_send_texts_without_sms_text_sends_no_sms_and_skips_missing_addresses()
    {
        $texts = ['email_subject' => 'S', 'email_body' => '<p>B</p>', 'sms_body' => null];
        $r = SystemMessenger::sendTexts('bulk:5', $texts, new MessageRecipient('No Email', null, '99111222'), [], ['channels' => ['email' => true, 'sms' => true]]);
        $this->assertSame(['email' => 'skipped_no_address', 'sms' => null], $r);
        Mail::assertNothingSent();
        $this->assertSame([], $this->sms);
    }

    public function test_send_texts_can_return_the_masked_failure_notes()
    {
        Mail::shouldReceive('to')->andThrow(new \Exception('SMTP 554 blocked for https://x.test/secret-link'));
        SystemMessenger::$smsSender = function () { return false; };
        $texts = ['email_subject' => 'S', 'email_body' => '<p>B</p>', 'sms_body' => 'Hi'];
        $to = new MessageRecipient('A', 'a@example.com', '99111222');
        $opts = ['channels' => ['email' => true, 'sms' => true], 'secret' => ['https://x.test/secret-link']];

        $this->assertSame(['email' => 'failed', 'sms' => 'failed'], SystemMessenger::sendTexts('bulk:one-off', $texts, $to, [], $opts));
        $r = SystemMessenger::sendTexts('bulk:one-off', $texts, $to, [], $opts + ['with_notes' => true]);
        $this->assertSame('failed', $r['email']);
        $this->assertSame('SMTP 554 blocked for [link hidden]', $r['notes']['email']);
        $this->assertSame('SMS gateway refused or SMS is disabled in settings', $r['notes']['sms']);
    }

    public function test_send_texts_adds_today_and_company_name()
    {
        $texts = ['email_subject' => 'X', 'email_body' => '<p>{company_name} {today}</p>', 'sms_body' => null];
        SystemMessenger::sendTexts('bulk:one-off', $texts, new MessageRecipient('A', 'a@example.com'), [], ['channels' => ['email' => true, 'sms' => false]]);
        Mail::assertSent(TemplatedMail::class, function ($m) {
            return strpos($m->bodyHtml, 'Al Habib &amp; Co. L.L.C ' . date('d/m/Y')) !== false;
        });
    }
}
