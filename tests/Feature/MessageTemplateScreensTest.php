<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\BackOffice\Entities\MessageTemplate as MT;
use Modules\BackOffice\Services\SystemTemplateDefaults;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class MessageTemplateScreensTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::find(1));
    }

    private function system()
    {
        return MT::where('key', 'pdc_bounce_are')->firstOrFail();
    }

    private function bulk()
    {
        return MT::create(['name' => 'Bulk one', 'kind' => 'bulk', 'audience' => 'tenant', 'sms_body' => 'Hi', 'is_active' => true]);
    }

    private function putTemplate(MT $t, array $extra)
    {
        return $this->put(route('message-templates.update', $t->id), $extra + [
            'name' => $t->name, 'audience' => $t->audience, 'email_subject' => $t->email_subject,
            'email_body' => $t->email_body, 'sms_body' => $t->sms_body, 'is_active' => 1,
        ]);
    }

    public function test_sms_switch_saved_for_system_templates_only()
    {
        $sys = $this->system();
        $this->putTemplate($sys, ['sms_enabled' => 1])->assertRedirect();
        $this->assertTrue($sys->fresh()->sms_enabled);
        $this->putTemplate($sys, [])->assertRedirect();
        $this->assertFalse($sys->fresh()->sms_enabled);

        $bulk = $this->bulk();
        $this->putTemplate($bulk, ['sms_enabled' => 1]);
        $this->assertFalse((bool) $bulk->fresh()->sms_enabled);
    }

    public function test_reset_restores_default_wording_but_keeps_switches()
    {
        $sys = $this->system();
        $sys->update(['email_subject' => 'Changed', 'sms_body' => 'Changed sms', 'is_active' => false, 'sms_enabled' => true]);
        $this->post(route('message-templates.reset', $sys->id))->assertRedirect(route('message-templates.edit', $sys->id));
        $d = SystemTemplateDefaults::get('pdc_bounce_are');
        $fresh = $sys->fresh();
        $this->assertSame([$d['email_subject'], $d['email_body'], $d['sms_body']], [$fresh->email_subject, $fresh->email_body, $fresh->sms_body]);
        $this->assertFalse($fresh->is_active);
        $this->assertTrue($fresh->sms_enabled);
    }

    public function test_reset_on_bulk_template_is_404()
    {
        $this->post(route('message-templates.reset', $this->bulk()->id))->assertStatus(404);
    }

    public function test_edit_page_shows_sms_switch_and_reset_for_system_only()
    {
        $html = $this->get(route('message-templates.edit', $this->system()->id))->assertOk()->getContent();
        $this->assertStringContainsString('name="sms_enabled"', $html);
        $this->assertStringContainsString('mt-reset-form', $html);
        $html = $this->get(route('message-templates.edit', $this->bulk()->id))->assertOk()->getContent();
        $this->assertStringNotContainsString('name="sms_enabled"', $html);
        $this->assertStringNotContainsString('mt-reset-form', $html);
    }

    public function test_list_shows_sms_on_badge()
    {
        $this->system()->update(['sms_enabled' => true]);
        $this->assertStringContainsString('SMS on', $this->get(route('message-templates.index'))->assertOk()->getContent());
    }

    // --- Project A checks, moved here from a scratch script (review minor M4) ---

    public function test_store_rules()
    {
        $this->post(route('message-templates.store'), ['name' => 'SMS only', 'audience' => 'tenant', 'sms_body' => 'Dear {tenant_name}', 'is_active' => 1])->assertRedirect();
        $t = MT::where('name', 'SMS only')->first();
        $this->assertSame(['bulk', null], [$t->kind, $t->key]);

        $this->post(route('message-templates.store'), ['name' => 'Empty', 'audience' => 'tenant'])->assertSessionHasErrors('channels');
        $this->post(route('message-templates.store'), ['name' => 'Blank body', 'audience' => 'any', 'email_subject' => 'Hi', 'email_body' => '<p><br></p>'])->assertSessionHasErrors('email_body');
        $this->post(route('message-templates.store'), ['name' => 'Dirty', 'audience' => 'any', 'email_subject' => 'Hi', 'email_body' => '<p onclick="x()">Hi<script>alert(1)</script></p>']);
        $this->assertSame('<p>Hi</p>', MT::where('name', 'Dirty')->value('email_body'));
    }

    public function test_system_template_key_audience_fixed_and_not_deletable()
    {
        $sys = $this->system();
        $this->putTemplate($sys, ['audience' => 'tenant', 'key' => 'hack', 'name' => 'Renamed']);
        $fresh = $sys->fresh();
        $this->assertSame(['pdc_bounce_are', 'staff', 'Renamed'], [$fresh->key, $fresh->audience, $fresh->name]);
        $this->delete(route('message-templates.destroy', $sys->id))->assertSessionHas('error');
        $this->assertNotNull(MT::find($sys->id));
    }

    public function test_view_only_user_can_list_and_preview_only()
    {
        $user = User::where('id', '<>', 1)->firstOrFail();
        \DB::table('model_has_roles')->where('model_id', $user->id)->delete();
        \DB::table('model_has_permissions')->where('model_id', $user->id)->delete();
        $role = Role::create(['name' => 'mt_view_only_tmp', 'guard_name' => 'web']);
        $role->givePermissionTo('view_message_templates');
        $user->assignRole($role);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->actingAs(User::find($user->id));
        $sys = $this->system();

        $this->get(route('message-templates.index'))->assertOk();
        $this->post(route('message-templates.preview'), ['id' => $sys->id])->assertOk();
        $this->get(route('message-templates.create'))->assertStatus(403);
        $this->get(route('message-templates.edit', $sys->id))->assertStatus(403);
        $this->post(route('message-templates.reset', $sys->id))->assertStatus(403);
        $this->delete(route('message-templates.destroy', $sys->id))->assertStatus(403);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
