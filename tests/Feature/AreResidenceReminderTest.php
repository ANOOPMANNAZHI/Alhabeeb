<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Modules\BackOffice\Emails\TemplatedMail;
use Modules\BackOffice\Entities\MessageLog;
use Modules\BackOffice\Entities\MessageTemplate;
use Modules\BackOffice\Services\StaffMessages;
use Modules\BackOffice\Services\SystemMessenger;

class AreResidenceReminderTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp()
    {
        parent::setUp();
        Mail::fake();
        SystemMessenger::$smsSender = function () { return true; };
        MessageTemplate::where('key', 'are_residence_reminder')->update(['is_active' => true]);
    }

    protected function tearDown()
    {
        Carbon::setTestNow();
        SystemMessenger::$smsSender = 'sendSms';
        parent::tearDown();
    }

    private function row($userId, $tenantId, $email = 'are@example.com')
    {
        return (object) ['are_user_id' => $userId, 'are_email' => $email, 'employee_name' => 'Munira', 'employee_contact_no' => '99111222',
            'tenant_id' => $tenantId, 'tenant_name' => 'Ali', 'tenant_contract_no' => 'TAG1', 'building_name' => 'Rex', 'tenant_resident_exp_date' => '2026-09-15'];
    }

    public function test_one_email_per_are_and_tenant_even_when_the_join_repeats_rows()
    {
        $sent = StaffMessages::areResidenceReminder([$this->row(5, 9), $this->row(5, 9), $this->row(5, 9), $this->row(6, 9, 'b@example.com'), $this->row(null, 9)]);
        $this->assertSame(2, $sent);
        Mail::assertSent(TemplatedMail::class, 2);
        Mail::assertSent(TemplatedMail::class, function ($m) {
            return $m->hasTo('are@example.com') && $m->subject === 'Residence ID expired - Ali (TAG1)'
                && strpos($m->bodyHtml, '15/09/2026') !== false;
        });
    }

    public function test_switched_off_by_default_sends_nothing()
    {
        MessageTemplate::where('key', 'are_residence_reminder')->update(['is_active' => false]);
        $before = MessageLog::where('template_key', 'are_residence_reminder')->count();
        $this->assertSame(0, StaffMessages::areResidenceReminder([$this->row(5, 9)]));
        Mail::assertNothingSent();
        $this->assertSame($before, MessageLog::where('template_key', 'are_residence_reminder')->count());
    }

    public function test_missing_row_uses_the_default_which_is_off()
    {
        \Illuminate\Support\Facades\DB::table('message_templates')->where('key', 'are_residence_reminder')->delete();
        $before = MessageLog::where('template_key', 'are_residence_reminder')->count();
        $this->assertSame(0, StaffMessages::areResidenceReminder([$this->row(5, 9)]));
        Mail::assertNothingSent();
        $this->assertSame($before, MessageLog::where('template_key', 'are_residence_reminder')->count());
    }

    public function test_cron_does_nothing_outside_09_00()
    {
        Carbon::setTestNow(Carbon::create(2026, 9, 30, 10, 0, 0));
        $before = MessageLog::where('template_key', 'are_residence_reminder')->count();
        (new \Modules\Sales\Http\Controllers\TenantController)->areReminderNotification();
        $this->assertSame($before, MessageLog::where('template_key', 'are_residence_reminder')->count());
        Mail::assertNothingSent();
    }
}
