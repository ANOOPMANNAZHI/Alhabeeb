<?php

namespace Modules\BackOffice\Tests\Unit;

use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Modules\BackOffice\Entities\OnboardingChecklistItem;
use Modules\BackOffice\Entities\TenantOnboarding as TenantOnboardingModel;
use Modules\BackOffice\Services\TenantOnboarding;
use Modules\Maintenance\Exceptions\InvalidStateException;
use Modules\Masters\Services\AreBuildings;
use Modules\Sales\Entities\TenantContract;
use Tests\TestCase;

class TenantOnboardingTest extends TestCase
{
    use DatabaseTransactions;

    private function are(): User
    {
        return User::role('are')->whereHas('areBuildings')->firstOrFail();
    }

    private function contractInScope(User $are): TenantContract
    {
        return TenantContract::where('work_flow_processes_code', 108)->where('tenant_contract_status', 1)
            ->whereIn('building_id', AreBuildings::forUser($are)->all())->latest('id')->firstOrFail();
    }

    public function test_create_assigns_the_building_are_and_complete_validates_required_items(): void
    {
        Notification::fake();
        $are = $this->are();
        $contract = $this->contractInScope($are);
        TenantOnboardingModel::where('tenant_contract_id', $contract->id)->delete();

        $o = TenantOnboarding::createForContract($contract);
        $this->assertNotNull($o);
        $this->assertSame($are->id, (int) $o->assigned_user_id);
        $this->assertNull(TenantOnboarding::createForContract($contract), 'idempotent');
        $this->assertTrue(TenantOnboarding::queue($are, 'open', null)->pluck('id')->contains($o->id));

        $items = OnboardingChecklistItem::active()->get();
        $required = $items->where('required', true);

        // partial draft is fine
        TenantOnboarding::saveEntries($o, $are, [['item_id' => $items->first()->id, 'done' => true]]);
        $this->assertSame(1, TenantOnboarding::detail($o->fresh())['progress']['done']);
        $this->assertSame($items->count(), TenantOnboarding::detail($o->fresh())['progress']['total']);

        $png = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';
        try {
            TenantOnboarding::complete($o->fresh(), $are, $png, null);
            $this->fail('required items missing should block completion');
        } catch (InvalidStateException $e) {
            $this->assertStringContainsString('required', strtolower($e->getMessage()));
        }

        TenantOnboarding::saveEntries($o, $are, $required->map(fn ($i) => ['item_id' => $i->id, 'done' => true, 'value' => 'x'])->values()->all());
        $done = TenantOnboarding::complete($o->fresh(), $are, $png, 'All good');
        $this->assertSame('completed', $done->status);
        $this->assertNotEmpty($done->pdf_path);
        $this->assertTrue(Storage::disk('local')->exists($done->pdf_path));
        $this->assertTrue(TenantOnboarding::queue($are, 'completed', null)->pluck('id')->contains($o->id));

        $admin = User::where('username', 'admin')->firstOrFail();
        TenantOnboarding::verify($done, $admin);
        $this->assertSame('verified', $done->fresh()->status);
        Storage::disk('local')->deleteDirectory("mobile/onboarding/{$o->id}");
    }

    public function test_sync_creates_missing_rows_for_recently_activated_contracts(): void
    {
        $are = $this->are();
        $contract = $this->contractInScope($are);
        TenantOnboardingModel::where('tenant_contract_id', $contract->id)->delete();
        $contract->touch();

        $n = TenantOnboarding::sync(Carbon::now()->subMinute());
        $this->assertGreaterThanOrEqual(1, $n);
        $this->assertDatabaseHas('tenant_onboardings', ['tenant_contract_id' => $contract->id, 'status' => 'open']);
    }
}
