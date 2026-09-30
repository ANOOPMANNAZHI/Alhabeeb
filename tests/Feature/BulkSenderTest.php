<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Modules\BackOffice\Emails\TemplatedMail;
use Modules\BackOffice\Entities\BulkSend;
use Modules\BackOffice\Entities\BulkSendRecipient;
use Modules\BackOffice\Entities\MessageLog;
use Modules\BackOffice\Services\Bulk\BulkSender;
use Modules\BackOffice\Services\SystemMessenger;

class BulkSenderTest extends TestCase
{
    use DatabaseTransactions;

    public $sms = [];

    protected function setUp()
    {
        parent::setUp();
        Mail::fake();
        $self = $this;
        SystemMessenger::$smsSender = function ($mobile, $msg) use ($self) { $self->sms[] = [$mobile, $msg]; return true; };
        // Other sends in the local DB must not interfere.
        DB::table('bulk_sends')->where('status', 'running')->update(['status' => 'paused']);
        DB::table('configuration')->where('configuration_settings', 'bulk_emails_per_minute')->update(['configuration_value' => '25']);
        DB::table('configuration')->where('configuration_settings', 'bulk_emails_per_day')->update(['configuration_value' => '8000']);
    }

    protected function tearDown()
    {
        Carbon::setTestNow();
        SystemMessenger::$smsSender = 'sendSms';
        parent::tearDown();
    }

    private function send($n, array $over = [], $emailFn = null)
    {
        $send = BulkSend::create($over + [
            'template_id' => null, 'audience' => 'staff', 'filters' => [], 'send_email' => true, 'send_sms' => false,
            'email_subject' => 'Hello {employee_name}', 'email_body' => '<p>Dear {employee_name}</p>', 'sms_body' => 'Hi {employee_name}',
            'status' => BulkSend::RUNNING, 'total' => $n, 'created_by' => 1,
        ]);
        for ($i = 1; $i <= $n; $i++) {
            $send->recipients()->create([
                'person_type' => 'staff', 'person_id' => $i, 'name' => 'P' . $i,
                'email' => $emailFn ? $emailFn($i) : 'p' . $i . '@example.com', 'mobile' => '9900000' . $i,
                'values' => ['employee_name' => 'P' . $i], 'status' => BulkSendRecipient::PENDING,
            ]);
        }
        return $send;
    }

    public function test_sends_25_per_run_then_finishes()
    {
        $send = $this->send(30);
        $r = BulkSender::run();
        $this->assertSame(25, $r['processed']);
        Mail::assertSent(TemplatedMail::class, 25);
        $this->assertSame(5, $send->pendingCount());
        $this->assertSame(25, (int) $send->fresh()->sent);
        BulkSender::run();
        $f = $send->fresh();
        $this->assertSame(BulkSend::DONE, $f->status);
        $this->assertNotNull($f->finished_at);
        $this->assertSame(30, MessageLog::where('related_type', 'bulk_send')->where('related_id', $send->id)->count());
        $this->assertSame('bulk:one-off', MessageLog::where('related_id', $send->id)->where('related_type', 'bulk_send')->value('template_key'));
        Mail::assertSent(TemplatedMail::class, function ($m) {
            return $m->hasTo('p1@example.com') && $m->subject === 'Hello P1';
        });
    }

    public function test_oldest_send_first_and_paused_or_cancelled_are_skipped()
    {
        $old = $this->send(20);
        $paused = $this->send(5, ['status' => BulkSend::PAUSED]);
        $cancelled = $this->send(5, ['status' => BulkSend::CANCELLED]);
        $new = $this->send(20);
        BulkSender::run();
        $this->assertSame(0, $old->pendingCount());
        $this->assertSame(15, $new->pendingCount());
        $this->assertSame(5, $paused->pendingCount());
        $this->assertSame(5, $cancelled->pendingCount());
    }

    public function test_sms_only_when_the_send_chose_it_and_skips()
    {
        $send = $this->send(3, ['send_sms' => true], function ($i) { return $i === 2 ? null : 'p' . $i . '@example.com'; });
        BulkSender::run();
        $this->assertCount(3, $this->sms);
        $row = $send->recipients()->where('person_id', 2)->first();
        $this->assertSame('sent', $row->status);
        $this->assertContains('email: skipped_no_address', $row->note);

        $this->sms = [];
        $noSms = $this->send(2, ['send_email' => true, 'send_sms' => false], function () { return null; });
        BulkSender::run();
        $this->assertSame([], $this->sms);
        $this->assertSame(2, $noSms->recipients()->where('status', 'skipped')->count());
        $this->assertSame(2, (int) $noSms->fresh()->skipped);
    }

    public function test_daily_limit_waits_and_continues_next_day()
    {
        DB::table('configuration')->where('configuration_settings', 'bulk_emails_per_day')->update(['configuration_value' => '10']);
        Carbon::setTestNow(Carbon::create(2026, 10, 1, 9, 0, 0));
        $send = $this->send(15);
        // a system email today does not count
        MessageLog::create(['template_key' => 'pdc_bounce_are', 'channel' => 'email', 'recipient' => 'x@example.com', 'status' => 'sent', 'created_at' => now()]);
        BulkSender::run();
        $this->assertSame(5, $send->pendingCount());
        BulkSender::run();
        $this->assertSame(5, $send->pendingCount());
        $this->assertSame(BulkSend::NOTE_DAILY_LIMIT, $send->fresh()->note);
        Carbon::setTestNow(Carbon::create(2026, 10, 2, 0, 1, 0));
        BulkSender::run();
        $this->assertSame(0, $send->pendingCount());
        $this->assertNull($send->fresh()->note);
    }

    public function test_safety_stop_after_5_email_failures_in_a_row()
    {
        Mail::shouldReceive('to')->andThrow(new \Exception('SMTP 554 blocked'));
        $send = $this->send(8);
        BulkSender::run();
        $f = $send->fresh();
        $this->assertSame(BulkSend::PAUSED, $f->status);
        $this->assertSame(BulkSend::NOTE_SAFETY_STOP, $f->note);
        $this->assertSame(5, (int) $f->failed);
        $this->assertSame(3, $send->pendingCount());
    }

    public function test_one_bad_row_is_marked_failed_and_the_run_goes_on()
    {
        $send = $this->send(3);
        $bad = $send->recipients()->where('person_id', 2)->first();
        // values that are not an array: passing them to sendTexts(array $values) throws a TypeError in BulkSender itself
        DB::table('bulk_send_recipients')->where('id', $bad->id)->update(['values' => '"not an array"']);

        $r = BulkSender::run();
        $this->assertSame(3, $r['processed']);
        $this->assertSame(1, $r['failed']);
        $this->assertSame(2, $r['sent']);
        $bad = $bad->fresh();
        $this->assertSame(BulkSendRecipient::FAILED, $bad->status);
        $this->assertStringStartsWith('error:', $bad->note);
        $this->assertNotNull($bad->processed_at);
        $this->assertSame(BulkSendRecipient::SENT, $send->recipients()->where('person_id', 3)->value('status'));
        $f = $send->fresh();
        $this->assertSame(1, (int) $f->failed);
        $this->assertSame(2, (int) $f->sent);
        $this->assertSame(BulkSend::DONE, $f->status);

        $again = BulkSender::run();
        $this->assertSame(0, $again['processed']);
        $this->assertSame(1, (int) $send->fresh()->failed);
    }

    public function test_pause_between_runs_stops_further_messages()
    {
        $send = $this->send(30);
        BulkSender::run();
        $send->update(['status' => BulkSend::PAUSED]);
        $r = BulkSender::run();
        $this->assertSame(0, $r['processed']);
        $this->assertSame(5, $send->pendingCount());
    }

    public function test_command_runs_and_is_scheduled_every_minute()
    {
        $this->send(2);
        $this->assertSame(0, \Artisan::call('bulk-messages:send'));
        Mail::assertSent(TemplatedMail::class, 2);
        $events = app(\Illuminate\Console\Scheduling\Schedule::class)->events();
        $found = false;
        foreach ($events as $e) {
            if (strpos($e->command, 'bulk-messages:send') !== false) {
                $found = true;
                $this->assertSame('* * * * *', $e->expression);
                $this->assertTrue($e->withoutOverlapping);
                // a crashed run must not block sending for the default 24 hours
                $this->assertSame(10, $e->expiresAt);
            }
        }
        $this->assertTrue($found, 'bulk-messages:send is scheduled');
    }
}
