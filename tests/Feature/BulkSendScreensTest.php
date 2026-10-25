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

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        SystemMessenger::$smsSender = function () { return true; };
        $this->actingAs(User::find(1));
    }

    protected function tearDown(): void
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
        $this->assertStringContainsString('Water shutdown', $html);
        $this->assertStringContainsString('Staff', $html);
        $html = $this->get(route('bulk-send.show', $send->id))->assertOk()->getContent();
        $this->assertStringContainsString('Alpha', $html);
        $this->assertStringContainsString('Beta', $html);
        $html = $this->get(route('bulk-send.show', [$send->id, 'status' => 'failed']))->assertOk()->getContent();
        $this->assertStringNotContainsString('Alpha', $html);
    }

    public function test_detail_has_status_counts()
    {
        $send = $this->send();
        $this->get(route('bulk-send.show', $send->id))->assertOk()
            ->assertViewHas('statusCounts', ['pending' => 1, 'sent' => 0, 'failed' => 1, 'skipped' => 0, 'cancelled' => 0]);
    }

    public function test_detail_has_tabs_with_counts_tiles_and_sent_log_link()
    {
        $send = $this->send();
        $html = $this->get(route('bulk-send.show', [$send->id, 'status' => 'failed']))->assertOk()->getContent();
        $this->assertMatchesRegularExpression('/href="[^"]*status=failed"[^>]*aria-current="page"[^>]*>\s*Failed\s*<span class="bs-tab-n">1<\/span>/', $html);
        $this->assertMatchesRegularExpression('/>\s*All\s*<span class="bs-tab-n">2<\/span>/', $html);
        $this->assertMatchesRegularExpression('/>\s*Sent\s*<span class="bs-tab-n">0<\/span>/', $html);
        $this->assertStringNotContainsString('<select name="status"', $html);
        $this->assertStringContainsString('bs-show-progress', $html); // the progress + counts line replaced the stat tiles
        $this->assertStringContainsString('0 of 2 processed', $html);
        $this->assertStringContainsString('email: failed', $html);
        $this->assertStringContainsString('bs-row-failed', $html);
        $this->assertStringContainsString('bs-badge-failed', $html);
        $this->assertStringContainsString(e(route('message-log.index', ['bulk_send' => $send->id])), $html);
        $this->assertStringContainsString('Open in Sent log', $html);
        $this->assertStringContainsString(route('bulk-send.pause', $send->id), $html);
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
        $this->assertStringContainsString('2 sent', $html);
        $this->assertStringContainsString('1 failed', $html);
        $this->assertStringContainsString('0 pending', $html);
        $this->assertStringNotContainsString('7 pending', $html);
    }

    public function test_list_shows_status_with_icon_and_text_and_accessible_progress()
    {
        $send = $this->send(['sent' => 1, 'failed' => 0, 'total' => 4]);
        $html = $this->get(route('bulk-send.index'))->getContent();
        $this->assertStringContainsString('1 of 4 processed', $html);
        $this->assertMatchesRegularExpression('/role="progressbar"[^>]*aria-valuenow="1"[^>]*aria-valuemax="4"/', $html);
        $this->assertMatchesRegularExpression('/bs-badge-running"><i class="fa [a-z-]+" aria-hidden="true"><\/i> Running</', $html);
        $this->assertStringContainsString('data-auto-refresh="1"', $html);
        $this->assertStringContainsString('auto-refresh on', $html);
        $this->assertStringContainsString('role="dialog"', $html);
        $this->assertStringContainsString('Cancel send #' . $send->id . '? Messages not sent yet will not be sent.', $html);
    }

    /** The list view with exactly these sends (no need to touch the other rows of the shared DB). */
    private function listHtml(array $sends)
    {
        $this->get(route('bulk-send.index')); // boots the request (route, session) the layout needs
        $page = new \Illuminate\Pagination\LengthAwarePaginator($sends, count($sends), 50, 1, ['path' => route('bulk-send.index')]);
        return view('backoffice::BulkSend.index', ['sends' => $page])->render();
    }

    public function test_list_does_not_auto_refresh_without_a_running_send()
    {
        $html = $this->listHtml([$this->send(['status' => BulkSend::DONE])]);
        $this->assertStringNotContainsString('data-auto-refresh="1"', $html);
        $this->assertStringContainsString('Done', $html);
    }

    public function test_list_empty_state()
    {
        $html = $this->listHtml([]);
        $this->assertStringContainsString('No sends yet', $html);
        $this->assertStringContainsString('bs-empty', $html);
        $this->assertStringContainsString(route('bulk-send.create'), $html);
    }

    public function test_resend_button_only_for_a_finished_send_not_yet_resent()
    {
        $send = $this->send(['failed' => 1]);
        $url = route('bulk-send.resend-failed', $send->id);
        $this->assertStringNotContainsString($url, $this->get(route('bulk-send.index'))->getContent());
        $this->post(route('bulk-send.cancel', $send->id));
        $html = $this->get(route('bulk-send.index'))->getContent();
        $this->assertStringContainsString($url, $html);
        $this->assertStringContainsString('Resend the failed messages of send #' . $send->id . '?', $html);
        $this->post($url)->assertSessionHas('success');
        $this->assertStringNotContainsString($url, $this->get(route('bulk-send.index'))->getContent());
    }

    public function test_sent_log_shows_a_readable_label_for_bulk_rows()
    {
        $t = MessageTemplate::create(['name' => 'Water notice', 'kind' => 'bulk', 'audience' => 'tenant', 'sms_body' => 'x', 'is_active' => true, 'created_by' => 1]);
        $this->assertSame('Bulk: Water notice', (new MessageLog(['template_key' => 'bulk:' . $t->id]))->template_label);
        $this->assertSame('Bulk: one-off message', (new MessageLog(['template_key' => 'bulk:one-off']))->template_label);
        MessageLog::create(['template_key' => 'bulk:' . $t->id, 'channel' => 'email', 'recipient' => 'z@example.com', 'status' => 'sent', 'created_at' => now()]);
        $this->assertStringContainsString('Bulk: Water notice', $this->get(route('message-log.index'))->assertOk()->getContent());
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
