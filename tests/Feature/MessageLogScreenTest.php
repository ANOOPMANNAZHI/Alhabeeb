<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\BackOffice\Entities\MessageLog;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class MessageLogScreenTest extends TestCase
{
    use DatabaseTransactions;

    private $rows = [];

    protected function setUp()
    {
        parent::setUp();
        $this->actingAs(User::find(1));
        $base = ['template_key' => 'pdc_bounce_are', 'created_at' => now()];
        $this->rows['sent'] = MessageLog::create($base + ['channel' => 'email', 'recipient_name' => 'Zeta One', 'recipient' => 'zeta.one@example.com',
            'subject' => 'Cheque bounced - X', 'body' => '<p>Hello body</p>', 'status' => 'sent']);
        $this->rows['failed'] = MessageLog::create($base + ['channel' => 'sms', 'recipient_name' => 'Zeta Two', 'recipient' => '99887766',
            'body' => 'Cheque sms text', 'status' => 'failed', 'note' => 'SMS gateway refused']);
        $this->rows['off'] = MessageLog::create($base + ['template_key' => 'pdc_bounce_tenant', 'channel' => 'email', 'recipient_name' => 'Zeta Three',
            'recipient' => 'zeta.three@example.com', 'status' => 'skipped_off']);
    }

    public function test_lists_and_filters()
    {
        $html = $this->get(route('message-log.index'))->assertOk()->getContent();
        foreach (['zeta.one@example.com', '99887766', 'zeta.three@example.com'] as $r) {
            $this->assertContains($r, $html);
        }
        $html = $this->get(route('message-log.index', ['status' => 'failed']))->getContent();
        $this->assertContains('99887766', $html);
        $this->assertNotContains('zeta.one@example.com', $html);
        $this->assertContains('SMS gateway refused', $html);

        $html = $this->get(route('message-log.index', ['q' => 'Zeta Three']))->getContent();
        $this->assertContains('zeta.three@example.com', $html);
        $this->assertNotContains('99887766', $html);

        $html = $this->get(route('message-log.index', ['channel' => 'sms', 'template' => 'pdc_bounce_are']))->getContent();
        $this->assertContains('99887766', $html);
        $this->assertNotContains('zeta.one@example.com', $html);

        $html = $this->get(route('message-log.index', ['from' => date('Y-m-d', strtotime('+1 day'))]))->getContent();
        $this->assertContains('Nothing was sent in this period', $html);
    }

    public function test_show_returns_row_json()
    {
        $this->get(route('message-log.show', $this->rows['sent']->id))->assertOk()
            ->assertJson(['subject' => 'Cheque bounced - X', 'body' => '<p>Hello body</p>', 'status_label' => 'Sent', 'channel' => 'email']);
        $this->get(route('message-log.show', 999999999))->assertStatus(404);
    }

    public function test_needs_view_message_log_permission()
    {
        $user = User::where('id', '<>', 1)->firstOrFail();
        \DB::table('model_has_roles')->where('model_id', $user->id)->delete();
        \DB::table('model_has_permissions')->where('model_id', $user->id)->delete();
        $role = Role::create(['name' => 'ml_none_tmp', 'guard_name' => 'web']);
        $role->givePermissionTo('view_message_templates');
        $user->assignRole($role);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->actingAs(User::find($user->id));
        $this->get(route('message-log.index'))->assertStatus(403);
        $this->get(route('message-log.show', $this->rows['sent']->id))->assertStatus(403);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
