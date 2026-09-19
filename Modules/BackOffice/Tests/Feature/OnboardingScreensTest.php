<?php

namespace Modules\BackOffice\Tests\Feature;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Modules\BackOffice\Entities\OnboardingChecklistItem;
use Modules\BackOffice\Entities\TenantOnboarding as TenantOnboardingModel;
use Modules\BackOffice\Services\TenantOnboarding;
use Modules\Masters\Services\AreBuildings;
use Modules\Sales\Entities\TenantContract;
use Tests\TestCase;

class OnboardingScreensTest extends TestCase
{
    use DatabaseTransactions;

    private function admin(): User
    {
        return User::where('username', 'admin')->firstOrFail();
    }

    private function are(): User
    {
        return User::role('are')->whereHas('areBuildings')->firstOrFail();
    }

    public function test_checklist_items_crud(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->get(route('onboarding-items.index'))->assertOk()->assertSee('Onboarding Checklist Items');
        $this->actingAs($admin)->get(route('onboarding-items.create'))->assertOk();

        $this->actingAs($admin)->post(route('onboarding-items.store'), [
            'group_name' => 'Keys', 'title' => 'Mailbox key handed over', 'input_type' => 'count', 'required' => 1, 'needs_photo' => 0, 'sort_order' => 35,
        ])->assertRedirect(route('onboarding-items.index'));
        $item = OnboardingChecklistItem::where('title', 'Mailbox key handed over')->firstOrFail();
        $this->assertSame('count', $item->input_type);

        $this->actingAs($admin)->get(route('onboarding-items.edit', $item))->assertOk()->assertSee('Mailbox key handed over');
        $this->actingAs($admin)->put(route('onboarding-items.update', $item), [
            'group_name' => 'Keys', 'title' => 'Mailbox key handed over', 'input_type' => 'count', 'required' => 0, 'needs_photo' => 1, 'sort_order' => 36, 'is_active' => 0,
        ])->assertRedirect();
        $this->assertFalse($item->fresh()->is_active);
        $this->assertFalse($item->fresh()->required);

        $this->flushSession(); // AuthenticateSession logs out when the session belongs to another user
        $this->actingAs($this->are())->get(route('onboarding-items.index'))->assertStatus(403);
    }

    public function test_onboarding_list_show_verify_reassign_pdf(): void
    {
        Notification::fake();
        $admin = $this->admin();
        $are = $this->are();
        $contract = TenantContract::where('work_flow_processes_code', 108)->where('tenant_contract_status', 1)
            ->whereIn('building_id', AreBuildings::forUser($are)->all())->latest('id')->firstOrFail();
        TenantOnboardingModel::where('tenant_contract_id', $contract->id)->delete();
        $o = TenantOnboarding::createForContract($contract);

        $this->actingAs($admin)->get(route('tenant-onboarding.index'))->assertOk()->assertSee($contract->tenant_contract_no);
        $this->actingAs($admin)->get(route('tenant-onboarding.index', ['status' => 'completed']))->assertOk()->assertDontSee($contract->tenant_contract_no);
        $this->actingAs($admin)->get(route('tenant-onboarding.show', $o))->assertOk()->assertSee($contract->tenant_contract_no);

        // verify is refused while open
        $this->actingAs($admin)->post(route('tenant-onboarding.verify', $o))->assertRedirect();
        $this->assertSame('open', $o->fresh()->status);

        $other = User::role('are')->where('id', '!=', $are->id)->firstOrFail();
        $this->actingAs($admin)->post(route('tenant-onboarding.reassign', $o), ['user_id' => $other->id])->assertRedirect();
        $this->assertSame($other->id, (int) $o->fresh()->assigned_user_id);

        $required = OnboardingChecklistItem::active()->where('required', true)->get();
        TenantOnboarding::saveEntries($o->fresh(), $other, $required->map(fn ($i) => ['item_id' => $i->id, 'done' => true])->values()->all());
        $png = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';
        TenantOnboarding::complete($o->fresh(), $other, $png, null);

        $this->actingAs($admin)->post(route('tenant-onboarding.verify', $o))->assertRedirect();
        $this->assertSame('verified', $o->fresh()->status);
        $this->actingAs($admin)->get(route('tenant-onboarding.pdf', $o))->assertOk()->assertHeader('content-type', 'application/pdf');

        $this->flushSession();
        $this->actingAs($are)->get(route('tenant-onboarding.index'))->assertOk();
        $this->actingAs($are)->post(route('tenant-onboarding.verify', $o))->assertStatus(403);
        Storage::disk('local')->deleteDirectory("mobile/onboarding/{$o->id}");
    }
}
