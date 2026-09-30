<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\BackOffice\Entities\BulkSend;
use Modules\BackOffice\Entities\BulkSendRecipient;

class BulkSendModelsTest extends TestCase
{
    use DatabaseTransactions;

    public function test_send_and_recipients_round_trip()
    {
        $send = BulkSend::create([
            'template_id' => null, 'audience' => 'staff', 'filters' => ['roles' => ['are']],
            'send_email' => true, 'send_sms' => false,
            'email_subject' => 'Hi {employee_name}', 'email_body' => '<p>Body</p>', 'sms_body' => null,
            'status' => BulkSend::RUNNING, 'total' => 1, 'created_by' => 1,
        ]);
        $send->recipients()->create([
            'person_type' => 'staff', 'person_id' => 1, 'name' => 'Admin', 'email' => 'a@example.com', 'mobile' => null,
            'values' => ['employee_name' => 'Admin'], 'status' => BulkSendRecipient::PENDING,
        ]);

        $fresh = BulkSend::find($send->id);
        $this->assertSame(['roles' => ['are']], $fresh->filters);
        $this->assertTrue($fresh->send_email);
        $this->assertFalse($fresh->send_sms);
        $this->assertSame('bulk:one-off', $fresh->logKey());
        $this->assertSame(['email_subject' => 'Hi {employee_name}', 'email_body' => '<p>Body</p>', 'sms_body' => null], $fresh->texts());
        $r = $fresh->recipients()->first();
        $this->assertSame(['employee_name' => 'Admin'], $r->values);
        $this->assertSame(0, (int) $fresh->sent);
        $this->assertSame(0, (int) $fresh->email_fail_streak);
    }

    public function test_log_key_uses_the_template_id()
    {
        $send = new BulkSend(['template_id' => 42]);
        $this->assertSame('bulk:42', $send->logKey());
    }

    public function test_rate_settings_exist()
    {
        $this->assertSame('25', prefixData('bulk_emails_per_minute')->configuration_value);
        $this->assertSame('8000', prefixData('bulk_emails_per_day')->configuration_value);
    }
}
