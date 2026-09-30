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
use Modules\BackOffice\Entities\MessageTemplate;
use Modules\BackOffice\Services\Bulk\RecipientFinder;
use Modules\BackOffice\Services\SystemMessenger;

class BulkSendComposeTest extends TestCase
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

    private function template($audience = 'staff', $sms = 'Hi {employee_name}')
    {
        return MessageTemplate::create(['name' => 'Staff notice', 'kind' => 'bulk', 'audience' => $audience,
            'email_subject' => 'Notice for {employee_name}', 'email_body' => '<p>Dear {employee_name}</p>', 'sms_body' => $sms, 'is_active' => true, 'created_by' => 1]);
    }

    public function test_create_page_lists_bulk_templates_audiences_locations_and_roles()
    {
        $t = $this->template();
        $html = $this->get(route('bulk-send.create'))->assertOk()->getContent();
        $this->assertContains('Staff notice', $html);
        $this->assertContains('Active tenants', $html);
        $this->assertContains('Past tenants', $html);
        $this->assertContains('backoffice_manager', $html);
        $this->assertContains(e((string) DB::table('locations')->where('locations_status', 1)->orderBy('locations_name')->value('locations_name')), $html);
        $this->assertNotContains((string) MessageTemplate::where('kind', 'system')->value('name'), $html);
    }

    public function test_count_matches_the_finder()
    {
        $this->post(route('bulk-send.count'), ['audience' => 'staff', 'roles' => ['super_admin']])
            ->assertOk()->assertJson(RecipientFinder::count('staff', ['roles' => ['super_admin']]));
    }

    public function test_review_shows_people_and_summary_and_validation_goes_back()
    {
        $t = $this->template();
        $html = $this->post(route('bulk-send.review'), ['template_id' => $t->id, 'audience' => 'staff', 'send_email' => 1])->assertOk()->getContent();
        $all = RecipientFinder::people('staff', []);
        $one = reset($all);
        $this->assertContains(e($one['name']), $html);
        $this->assertContains('Start sending', $html);
        $this->post(route('bulk-send.review'), ['template_id' => $t->id, 'audience' => 'tenant_active', 'send_email' => 1])
            ->assertRedirect()->assertSessionHasErrors('template_id');
    }

    public function test_search_and_preview()
    {
        $t = $this->template();
        $all = RecipientFinder::people('staff', []);
        $one = reset($all);
        $key = 'staff:' . $one['person_id'];
        $this->get(route('bulk-send.search', ['audience' => 'staff', 'q' => mb_substr($one['name'], 0, 3)]))
            ->assertOk()->assertJsonFragment(['key' => $key]);
        $this->post(route('bulk-send.preview'), ['template_id' => $t->id, 'audience' => 'staff', 'key' => $key])
            ->assertOk()->assertJson(['subject' => 'Notice for ' . $one['values']['employee_name']]);
    }

    public function test_store_starts_the_send_with_exclusions()
    {
        $t = $this->template();
        $keys = array_keys(RecipientFinder::people('staff', []));
        $this->post(route('bulk-send.store'), ['template_id' => $t->id, 'audience' => 'staff', 'send_email' => 1, 'exclude' => [$keys[0]]])
            ->assertRedirect(route('bulk-send.index'))->assertSessionHas('success');
        $send = BulkSend::latest('id')->first();
        $this->assertSame(count($keys) - 1, (int) $send->total);
        $this->assertFalse($send->send_sms);
        Mail::assertNothingSent();
    }

    public function test_sms_needs_sms_text_and_one_off_works()
    {
        $t = $this->template('staff', null);
        $this->post(route('bulk-send.store'), ['template_id' => $t->id, 'audience' => 'staff', 'send_email' => 1, 'send_sms' => 1])
            ->assertRedirect()->assertSessionHasErrors('sms_body');
        $this->post(route('bulk-send.store'), ['audience' => 'staff', 'roles' => ['super_admin'], 'send_email' => 1,
            'email_subject' => 'Quick note', 'email_body' => '<p>Hello {employee_name}</p>'])->assertRedirect(route('bulk-send.index'));
        $this->assertSame('bulk:one-off', BulkSend::latest('id')->first()->logKey());
    }

    public function test_compose_routes_need_the_permission()
    {
        $user = User::where('id', '<>', 1)->firstOrFail();
        DB::table('model_has_roles')->where('model_id', $user->id)->delete();
        DB::table('model_has_permissions')->where('model_id', $user->id)->delete();
        $role = Role::create(['name' => 'bs_none_tmp2', 'guard_name' => 'web']);
        $role->givePermissionTo('view_message_templates');
        $user->assignRole($role);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->actingAs(User::find($user->id));
        $before = BulkSend::count();
        $this->get(route('bulk-send.create'))->assertStatus(403);
        $this->post(route('bulk-send.count'), ['audience' => 'staff'])->assertStatus(403);
        $this->post(route('bulk-send.review'), ['audience' => 'staff'])->assertStatus(403);
        $this->get(route('bulk-send.search', ['audience' => 'staff', 'q' => 'a']))->assertStatus(403);
        $this->post(route('bulk-send.preview'), ['audience' => 'staff'])->assertStatus(403);
        $this->post(route('bulk-send.store'), ['audience' => 'staff', 'send_email' => 1, 'email_subject' => 'a', 'email_body' => '<p>b</p>'])->assertStatus(403);
        $this->assertSame($before, BulkSend::count());
    }
}
