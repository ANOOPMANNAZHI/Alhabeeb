<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Validation\ValidationException;
use Modules\BackOffice\Entities\BulkSend;
use Modules\BackOffice\Entities\BulkSendRecipient;
use Modules\BackOffice\Entities\MessageTemplate;
use Modules\BackOffice\Services\Bulk\BulkSendService as S;
use Modules\BackOffice\Services\Bulk\RecipientFinder;

class BulkSendServiceTest extends TestCase
{
    use DatabaseTransactions;

    private function template($audience, $sms = 'Hi {employee_name}', $active = true)
    {
        return MessageTemplate::create(['name' => 'T ' . $audience, 'kind' => 'bulk', 'audience' => $audience,
            'email_subject' => 'Notice', 'email_body' => '<p>Dear {employee_name}</p>', 'sms_body' => $sms, 'is_active' => $active, 'created_by' => 1]);
    }

    private function base(array $over = [])
    {
        return $over + ['template_id' => null, 'audience' => 'staff', 'filters' => [], 'send_email' => true, 'send_sms' => false, 'exclude' => [], 'add' => []];
    }

    private function errors(array $in)
    {
        try {
            S::start($in, 1);
        } catch (ValidationException $e) {
            return $e->errors();
        }
        $this->fail('No validation error');
    }

    public function test_start_freezes_texts_and_writes_one_row_per_person()
    {
        $t = $this->template('staff');
        $send = S::start($this->base(['template_id' => $t->id]), 1);
        $expected = count(RecipientFinder::people('staff', []));
        $this->assertSame(BulkSend::RUNNING, $send->status);
        $this->assertSame($expected, (int) $send->total);
        $this->assertSame($expected, $send->recipients()->where('status', 'pending')->count());
        $t->update(['email_subject' => 'Changed']);
        $this->assertSame('Notice', BulkSend::find($send->id)->email_subject);
        $this->assertSame(1, (int) $send->created_by);
    }

    public function test_exclude_and_add()
    {
        $t = $this->template('staff');
        $people = RecipientFinder::people('staff', []);
        $keys = array_keys($people);
        $send = S::start($this->base(['template_id' => $t->id, 'exclude' => [$keys[0]]]), 1);
        $this->assertSame(count($keys) - 1, (int) $send->total);
        $this->assertFalse($send->recipients()->where('person_id', $people[$keys[0]]['person_id'])->exists());

        $role = \DB::table('roles')->where('name', 'super_admin')->value('name');
        $few = RecipientFinder::people('staff', ['roles' => [$role]]);
        $outsider = null;
        foreach ($keys as $k) {
            if (!isset($few[$k])) { $outsider = $k; break; }
        }
        if ($outsider) {
            $send2 = S::start($this->base(['template_id' => $t->id, 'filters' => ['roles' => [$role]], 'add' => [$outsider, 'staff:99999999']]), 1);
            $this->assertSame(count($few) + 1, (int) $send2->total);
        }
    }

    public function test_added_people_load_the_audience_only_once()
    {
        $t = $this->template('staff');
        $role = \DB::table('roles')->where('name', 'super_admin')->value('name');
        $few = RecipientFinder::people('staff', ['roles' => [$role]]);
        $outsiders = array_values(array_diff(array_keys(RecipientFinder::people('staff', [])), array_keys($few)));
        if (count($outsiders) < 3) {
            $this->markTestSkipped('needs 3 staff outside super_admin');
        }
        $queries = function (array $add) use ($t, $role) {
            \DB::flushQueryLog();
            \DB::enableQueryLog();
            S::start($this->base(['template_id' => $t->id, 'filters' => ['roles' => [$role]], 'add' => $add]), 1);
            $n = count(\DB::getQueryLog());
            \DB::disableQueryLog();
            return $n;
        };
        $one = $queries([$outsiders[0]]);
        $three = $queries([$outsiders[0], $outsiders[1], $outsiders[2]]);
        $this->assertSame($one, $three);
    }

    public function test_audience_mismatch_is_rejected_any_fits_all()
    {
        $t = $this->template('tenant');
        $e = $this->errors($this->base(['template_id' => $t->id, 'audience' => 'staff']));
        $this->assertSame(['This template is for Tenants and cannot be sent to Staff.'], $e['template_id']);
        $any = $this->template('any', null);
        $this->assertInstanceOf(BulkSend::class, S::start($this->base(['template_id' => $any->id]), 1));
    }

    public function test_channel_rules()
    {
        $noSms = $this->template('staff', null);
        $this->assertSame(['This message has no SMS text.'], $this->errors($this->base(['template_id' => $noSms->id, 'send_sms' => true]))['sms_body']);
        $this->assertSame(['Choose email, SMS or both.'], $this->errors($this->base(['template_id' => $noSms->id, 'send_email' => false]))['channels']);
        $smsOnly = MessageTemplate::create(['name' => 'S', 'kind' => 'bulk', 'audience' => 'staff', 'sms_body' => 'Hi', 'is_active' => true, 'created_by' => 1]);
        $this->assertSame(['Email needs a subject and a body.'], $this->errors($this->base(['template_id' => $smsOnly->id]))['email_subject']);
        $send = S::start($this->base(['template_id' => $smsOnly->id, 'send_email' => false, 'send_sms' => true]), 1);
        $this->assertFalse($send->send_email);
        $this->assertTrue($send->send_sms);
    }

    public function test_inactive_or_system_template_is_rejected()
    {
        $off = $this->template('staff', 'x', false);
        $this->assertSame(['Choose an active Bulk template.'], $this->errors($this->base(['template_id' => $off->id]))['template_id']);
        $system = MessageTemplate::where('kind', 'system')->first();
        $this->assertSame(['Choose an active Bulk template.'], $this->errors($this->base(['template_id' => $system->id]))['template_id']);
    }

    public function test_one_off_message_and_empty_audience()
    {
        $send = S::start($this->base(['email_subject' => 'One off', 'email_body' => '<p>Hello {employee_name}</p><script>x</script>']), 1);
        $this->assertNull($send->template_id);
        $this->assertSame('bulk:one-off', $send->logKey());
        $this->assertNotContains('<script>', $send->email_body);
        $this->assertSame(['Choose who to send to.'], $this->errors($this->base(['audience' => 'aliens', 'email_subject' => 'a', 'email_body' => '<p>b</p>']))['audience']);
        $this->assertSame(['Nobody to send to.'], $this->errors($this->base(['audience' => 'tenant_active', 'filters' => ['locations' => [999999]], 'email_subject' => 'a', 'email_body' => '<p>b</p>']))['recipients']);
    }

    public function test_pause_resume_cancel_and_resend_failed()
    {
        $t = $this->template('staff');
        $send = S::start($this->base(['template_id' => $t->id]), 1);
        S::pause($send);
        $this->assertSame(BulkSend::PAUSED, $send->fresh()->status);
        $send->update(['note' => BulkSend::NOTE_SAFETY_STOP, 'email_fail_streak' => 5]);
        S::resume($send->fresh());
        $f = $send->fresh();
        $this->assertSame([BulkSend::RUNNING, null, 0], [$f->status, $f->note, (int) $f->email_fail_streak]);

        $first = $send->recipients()->orderBy('id')->first();
        $first->update(['status' => BulkSendRecipient::FAILED, 'note' => 'email: failed']);
        S::cancel($send->fresh());
        $c = $send->fresh();
        $this->assertSame(BulkSend::CANCELLED, $c->status);
        $this->assertNotNull($c->finished_at);
        $this->assertSame(0, $c->recipients()->where('status', 'pending')->count());

        $again = S::resendFailed($c, 1);
        $this->assertSame(1, (int) $again->total);
        $this->assertSame($first->email, $again->recipients()->first()->email);
        $this->assertSame($c->email_subject, $again->email_subject);
        $this->assertSame($again->id, (int) $c->fresh()->resent_send_id);

        $again->recipients()->update(['status' => 'sent']);
        $again->update(['status' => BulkSend::DONE]);
        $this->assertSame(['No failed messages to resend.'], $this->resendErrors($again->fresh()));
    }

    private function resendErrors(BulkSend $send)
    {
        try {
            S::resendFailed($send, 1);
        } catch (ValidationException $e) {
            return $e->errors()['recipients'];
        }
        $this->fail('No validation error');
    }

    public function test_resend_only_for_a_finished_send_and_only_once()
    {
        $t = $this->template('staff');
        $send = S::start($this->base(['template_id' => $t->id]), 1);
        $send->recipients()->orderBy('id')->first()->update(['status' => BulkSendRecipient::FAILED, 'note' => 'email: failed']);
        $before = BulkSend::count();

        $this->assertSame(['Resend is only possible for a finished or cancelled send.'], $this->resendErrors($send->fresh()));
        S::pause($send);
        $this->assertSame(['Resend is only possible for a finished or cancelled send.'], $this->resendErrors($send->fresh()));
        $this->assertSame($before, BulkSend::count());

        $send->update(['status' => BulkSend::DONE]);
        $stale = $send->fresh();
        $new = S::resendFailed($send->fresh(), 1);
        $this->assertSame($new->id, (int) $send->fresh()->resent_send_id);
        // a second click (even from a page loaded before the first resend) is refused
        $this->assertSame(['Failed messages of this send were already resent (send #' . $new->id . ').'], $this->resendErrors($stale));
        $this->assertSame($before + 1, BulkSend::count());
    }
}
