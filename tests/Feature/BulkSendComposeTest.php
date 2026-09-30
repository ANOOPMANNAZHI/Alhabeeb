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

    public function test_email_box_keeps_an_sms_only_choice_after_a_validation_error()
    {
        $this->assertRegExp('/id="bs-email"\s+checked/', $this->get(route('bulk-send.create'))->getContent());
        $html = $this->withSession(['_old_input' => ['audience' => 'staff', 'send_sms' => '1']])
            ->get(route('bulk-send.create'))->assertOk()->getContent();
        $this->assertNotRegExp('/id="bs-email"\s+checked/', $html);
        $this->assertRegExp('/id="bs-send-sms"\s+checked/', $html);
    }

    public function test_create_page_has_steps_cards_toggles_and_a_summary()
    {
        $t = $this->template();
        $location = DB::table('locations')->where('locations_status', 1)->orderBy('locations_name')->first();
        $html = $this->withSession(['_old_input' => ['template_id' => (string) $t->id, 'audience' => 'landlord', 'locations' => [(string) $location->id], 'send_email' => '1']])
            ->get(route('bulk-send.create'))->assertOk()->getContent();
        $this->assertContains('class="bs-stepper"', $html);
        $this->assertRegExp('/<input type="radio" name="template_id" value="' . $t->id . '"[^>]*checked/', $html);
        $this->assertRegExp('/<input type="radio" name="template_id" value=""/', $html);
        $this->assertContains('Write a one-off message', $html);
        $this->assertContains('SMS (costs money)', $html);
        $this->assertRegExp('/<input type="radio" name="audience" value="landlord"[^>]*checked/', $html);
        $this->assertContains('data-count-for="staff"', $html);
        $this->assertRegExp('/<input type="checkbox" name="locations\[\]" value="' . $location->id . '"[^>]*checked/', $html);
        $this->assertRegExp('/<input type="checkbox" name="roles\[\]" value="super_admin"/', $html);
        $this->assertContains(json_encode(route('bulk-send.audience-counts'), 15), $html); // as @json writes it
        $this->assertContains(json_encode(route('bulk-send.place-counts'), 15), $html);
        $this->assertRegExp('/id="bs-summary-card"[^>]*data-per-minute="([1-9]|[12][0-9]|30)"/', $html);
        $this->assertRegExp('/<button type="submit"[^>]*>[^<]*(<i[^>]*><\/i>)?\s*Review/', $html);
    }

    public function test_create_page_shows_errors_next_to_their_section()
    {
        $errors = (new \Illuminate\Support\ViewErrorBag())->put('default', new \Illuminate\Support\MessageBag([
            'template_id' => ['Choose an active Bulk template.'], 'recipients' => ['Nobody to send to.'],
        ]));
        $html = $this->withSession(['errors' => $errors, '_old_input' => ['audience' => 'staff']])->get(route('bulk-send.create'))->assertOk()->getContent();
        $this->assertRegExp('/id="bs-errors"[^>]*role="alert"/', $html);
        $this->assertRegExp('/id="bs-err-message"[^>]*>.*Choose an active Bulk template\./s', $html);
        $this->assertRegExp('/id="bs-err-recipients"[^>]*>.*Nobody to send to\./s', $html);
        $this->assertContains('href="#bs-sec-message"', $html);
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
        $this->withSession(['bulk_send_tokens' => ['t1']])
            ->post(route('bulk-send.store'), ['template_id' => $t->id, 'audience' => 'staff', 'send_email' => 1, 'exclude' => [$keys[0]], 'send_token' => 't1'])
            ->assertRedirect(route('bulk-send.index'))->assertSessionHas('success');
        $send = BulkSend::latest('id')->first();
        $this->assertSame(count($keys) - 1, (int) $send->total);
        $this->assertFalse($send->send_sms);
        Mail::assertNothingSent();
    }

    public function test_sms_needs_sms_text_and_one_off_works()
    {
        $t = $this->template('staff', null);
        $this->withSession(['bulk_send_tokens' => ['t1', 't2']])
            ->post(route('bulk-send.store'), ['template_id' => $t->id, 'audience' => 'staff', 'send_email' => 1, 'send_sms' => 1, 'send_token' => 't1'])
            ->assertRedirect()->assertSessionHasErrors('sms_body');
        $this->post(route('bulk-send.store'), ['audience' => 'staff', 'roles' => ['super_admin'], 'send_email' => 1,
            'email_subject' => 'Quick note', 'email_body' => '<p>Hello {employee_name}</p>', 'send_token' => 't2'])->assertRedirect(route('bulk-send.index'));
        $this->assertSame('bulk:one-off', BulkSend::latest('id')->first()->logKey());
    }

    public function test_review_puts_a_one_time_token_in_the_form_and_session()
    {
        $t = $this->template();
        $html = $this->post(route('bulk-send.review'), ['template_id' => $t->id, 'audience' => 'staff', 'send_email' => 1])->assertOk()->getContent();
        $tokens = session('bulk_send_tokens');
        $this->assertCount(1, $tokens);
        $this->assertSame(40, strlen($tokens[0]));
        $this->assertContains('name="send_token" value="' . $tokens[0] . '"', $html);
        for ($i = 0; $i < 12; $i++) {
            $this->post(route('bulk-send.review'), ['template_id' => $t->id, 'audience' => 'staff', 'send_email' => 1]);
        }
        $this->assertCount(10, session('bulk_send_tokens'));
    }

    public function test_store_twice_with_the_same_token_starts_one_send()
    {
        $t = $this->template();
        $before = BulkSend::count();
        $post = ['template_id' => $t->id, 'audience' => 'staff', 'roles' => ['super_admin'], 'send_email' => 1, 'send_token' => 't1'];
        $this->withSession(['bulk_send_tokens' => ['t1']])->post(route('bulk-send.store'), $post)
            ->assertRedirect(route('bulk-send.index'))->assertSessionHas('success');
        $this->post(route('bulk-send.store'), $post)->assertRedirect(route('bulk-send.index'))
            ->assertSessionHas('error', 'This send was already started (or the page expired). Check the list below.');
        $this->assertSame($before + 1, BulkSend::count());
    }

    public function test_store_without_a_valid_token_starts_nothing()
    {
        $t = $this->template();
        $before = BulkSend::count();
        $post = ['template_id' => $t->id, 'audience' => 'staff', 'roles' => ['super_admin'], 'send_email' => 1];
        $this->withSession(['bulk_send_tokens' => ['t1']])->post(route('bulk-send.store'), $post)
            ->assertRedirect(route('bulk-send.index'))->assertSessionHas('error');
        $this->post(route('bulk-send.store'), $post + ['send_token' => 'other'])
            ->assertRedirect(route('bulk-send.index'))->assertSessionHas('error');
        $this->assertSame($before, BulkSend::count());
    }

    public function test_audience_counts_match_the_finder()
    {
        $json = $this->get(route('bulk-send.audience-counts'))->assertOk()->json();
        $this->assertSame(['tenant_active', 'tenant_past', 'landlord', 'staff'], array_keys($json));
        foreach ($json as $audience => $n) {
            $this->assertSame(RecipientFinder::count($audience, [])['total'], $n, $audience);
        }
    }

    public function test_place_counts_per_building_location_and_role()
    {
        $locationOf = DB::table('buildings')->pluck('location_id', 'id')->all();
        foreach (['tenant_active', 'landlord'] as $audience) {
            $json = $this->get(route('bulk-send.place-counts', ['audience' => $audience]))->assertOk()->json();
            $this->assertSame(['locations', 'buildings'], array_keys($json));
            $byBuilding = [];
            $byLocation = [];
            foreach (RecipientFinder::people($audience, []) as $p) {
                $b = $p['building_id'];
                if (!$b) {
                    continue;
                }
                $byBuilding[$b] = (isset($byBuilding[$b]) ? $byBuilding[$b] : 0) + 1;
                $l = isset($locationOf[$b]) ? (int) $locationOf[$b] : 0;
                if ($l) {
                    $byLocation[$l] = (isset($byLocation[$l]) ? $byLocation[$l] : 0) + 1;
                }
            }
            $gotB = $json['buildings'];
            $gotL = $json['locations'];
            ksort($byBuilding); ksort($gotB); ksort($byLocation); ksort($gotL);
            $this->assertSame($byBuilding, $gotB, $audience);
            $this->assertSame($byLocation, $gotL, $audience);
        }
        $json = $this->get(route('bulk-send.place-counts', ['audience' => 'staff']))->assertOk()->json();
        $this->assertSame(['roles'], array_keys($json));
        $this->assertSame(RecipientFinder::count('staff', ['roles' => ['super_admin']])['total'], $json['roles']['super_admin']);
        $this->assertSame('{"locations":{},"buildings":{}}', $this->get(route('bulk-send.place-counts', ['audience' => 'nobody']))->assertOk()->getContent());
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
        $this->get(route('bulk-send.audience-counts'))->assertStatus(403);
        $this->get(route('bulk-send.place-counts', ['audience' => 'staff']))->assertStatus(403);
        $this->post(route('bulk-send.store'), ['audience' => 'staff', 'send_email' => 1, 'email_subject' => 'a', 'email_body' => '<p>b</p>'])->assertStatus(403);
        $this->assertSame($before, BulkSend::count());
    }
}
