<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Modules\BackOffice\Entities\BulkSend;
use Modules\BackOffice\Entities\BulkSendRecipient;
use Modules\BackOffice\Entities\MessageLog;
use Modules\BackOffice\Entities\MessageTemplate;
use Modules\BackOffice\Services\SystemMessenger;

class BulkSendScreensTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp()
    {
        parent::setUp();
        Mail::fake();
        SystemMessenger::$smsSender = function () { return true; };
        $this->actingAs(User::find(1));
    }

    protected function tearDown()
    {
        SystemMessenger::$smsSender = 'sendSms';
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        parent::tearDown();
    }

    private function send(array $over = [])
    {
        $send = BulkSend::create($over + [
            'template_id' => null, 'audience' => 'staff', 'filters' => [], 'send_email' => true, 'send_sms' => false,
            'email_subject' => 'Water shutdown', 'email_body' => '<p>x</p>', 'status' => BulkSend::RUNNING, 'total' => 2, 'created_by' => 1,
        ]);
        $send->recipients()->create(['person_type' => 'staff', 'person_id' => 1, 'name' => 'Alpha', 'email' => 'a@example.com', 'values' => [], 'status' => 'pending']);
        $send->recipients()->create(['person_type' => 'staff', 'person_id' => 2, 'name' => 'Beta', 'email' => 'b@example.com', 'values' => [], 'status' => 'failed', 'note' => 'email: failed']);
        return $send;
    }

    public function test_menu_and_permission_exist_for_the_two_roles()
    {
        $this->assertTrue(DB::table('menu')->where('route_name', 'bulk-send.index')->exists());
        $pid = DB::table('permissions')->where('name', 'bulk_message_send')->value('id');
        $this->assertNotNull($pid);
        $roles = DB::table('role_has_permissions')->join('roles', 'roles.id', '=', 'role_has_permissions.role_id')
            ->where('permission_id', $pid)->pluck('roles.name')->all();
        sort($roles);
        $this->assertSame(['backoffice_manager', 'super_admin'], $roles);
    }

    public function test_list_and_detail()
    {
        $send = $this->send();
        $html = $this->get(route('bulk-send.index'))->assertOk()->getContent();
        $this->assertContains('Water shutdown', $html);
        $this->assertContains('Staff', $html);
        $html = $this->get(route('bulk-send.show', $send->id))->assertOk()->getContent();
        $this->assertContains('Alpha', $html);
        $this->assertContains('Beta', $html);
        $html = $this->get(route('bulk-send.show', [$send->id, 'status' => 'failed']))->assertOk()->getContent();
        $this->assertNotContains('Alpha', $html);
    }

    public function test_detail_has_status_counts()
    {
        $send = $this->send();
        $this->get(route('bulk-send.show', $send->id))->assertOk()
            ->assertViewHas('statusCounts', ['pending' => 1, 'sent' => 0, 'failed' => 1, 'skipped' => 0, 'cancelled' => 0]);
    }

    public function test_actions()
    {
        $send = $this->send();
        $this->post(route('bulk-send.pause', $send->id))->assertRedirect(route('bulk-send.index'));
        $this->assertSame('paused', $send->fresh()->status);
        $this->post(route('bulk-send.resume', $send->id))->assertRedirect(route('bulk-send.index'));
        $this->assertSame('running', $send->fresh()->status);
        $this->post(route('bulk-send.cancel', $send->id))->assertRedirect(route('bulk-send.index'));
        $this->assertSame('cancelled', $send->fresh()->status);
        $before = BulkSend::count();
        $this->post(route('bulk-send.resend-failed', $send->id))->assertRedirect(route('bulk-send.index'))->assertSessionHas('success');
        $this->assertSame($before + 1, BulkSend::count());
    }

    public function test_cancelled_send_shows_nothing_pending()
    {
        $send = $this->send(['status' => BulkSend::CANCELLED, 'total' => 10, 'sent' => 2, 'failed' => 1]);
        $html = $this->get(route('bulk-send.index'))->getContent();
        $this->assertContains('2 / 1 / 0 / 0', $html);
        $this->assertNotContains('2 / 1 / 0 / 7', $html);
    }

    public function test_resend_button_only_for_a_finished_send_not_yet_resent()
    {
        $send = $this->send(['failed' => 1]);
        $url = route('bulk-send.resend-failed', $send->id);
        $this->assertNotContains($url, $this->get(route('bulk-send.index'))->getContent());
        $this->post(route('bulk-send.cancel', $send->id));
        $html = $this->get(route('bulk-send.index'))->getContent();
        $this->assertContains($url, $html);
        $this->assertContains('Resend the failed messages of send #' . $send->id . '?', $html);
        $this->post($url)->assertSessionHas('success');
        $this->assertNotContains($url, $this->get(route('bulk-send.index'))->getContent());
    }

    public function test_sent_log_shows_a_readable_label_for_bulk_rows()
    {
        $t = MessageTemplate::create(['name' => 'Water notice', 'kind' => 'bulk', 'audience' => 'tenant', 'sms_body' => 'x', 'is_active' => true, 'created_by' => 1]);
        $this->assertSame('Bulk: Water notice', (new MessageLog(['template_key' => 'bulk:' . $t->id]))->template_label);
        $this->assertSame('Bulk: one-off message', (new MessageLog(['template_key' => 'bulk:one-off']))->template_label);
        MessageLog::create(['template_key' => 'bulk:' . $t->id, 'channel' => 'email', 'recipient' => 'z@example.com', 'status' => 'sent', 'created_at' => now()]);
        $this->assertContains('Bulk: Water notice', $this->get(route('message-log.index'))->assertOk()->getContent());
    }

    public function test_every_route_needs_the_permission()
    {
        $send = $this->send();
        $user = User::where('id', '<>', 1)->firstOrFail();
        DB::table('model_has_roles')->where('model_id', $user->id)->delete();
        DB::table('model_has_permissions')->where('model_id', $user->id)->delete();
        $role = Role::create(['name' => 'bs_none_tmp', 'guard_name' => 'web']);
        $role->givePermissionTo('view_message_templates');
        $user->assignRole($role);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->actingAs(User::find($user->id));
        $this->get(route('bulk-send.index'))->assertStatus(403);
        $this->get(route('bulk-send.show', $send->id))->assertStatus(403);
        foreach (['pause', 'resume', 'cancel', 'resend-failed'] as $a) {
            $this->post(route('bulk-send.' . $a, $send->id))->assertStatus(403);
        }
        $this->assertSame('running', $send->fresh()->status);
    }
}
