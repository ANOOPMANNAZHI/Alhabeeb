<?php

namespace Tests\Feature;

use App\User;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\BackOffice\Entities\ReceivableFollowup;
use Modules\BackOffice\Services\ReceivableFigures;
use Modules\Sales\Entities\TenantContract;

class NoComputeFigures extends ReceivableFigures
{
    public function compute($asOn)
    {
        throw new \RuntimeException('figures must come from the seeded cache in tests');
    }
}

class ReceivableFollowupTest extends TestCase
{
    use DatabaseTransactions;

    private $asOn;
    private $contracts;

    protected function setUp()
    {
        parent::setUp();
        $this->app->bind(ReceivableFigures::class, NoComputeFigures::class);
        $this->actingAs(User::find(1));
        list($m, $y) = ReceivableFigures::lastMonth();
        $this->asOn = ReceivableFigures::asOnDate($m, $y);
        // three real contracts in two different active buildings with different management types
        $this->contracts = TenantContract::join('buildings', 'buildings.id', '=', 'tenant_contracts.building_id')
            ->where('buildings.building_status', 1)->whereIn('buildings.management_id', [1, 2])
            ->whereHas('tenant')->whereNotNull('tenant_contracts.unit_id')
            ->orderBy('tenant_contracts.id', 'desc')->limit(400)->get(['tenant_contracts.*', 'buildings.management_id'])
            ->unique('building_id')->take(3)->values();
        $this->seed3([500.0, 1500.0, 900.0]);
    }

    private function seed3(array $balances)
    {
        $rows = [];
        foreach ($this->contracts as $i => $c) {
            $rows[$c->id] = ['due' => $balances[$i] + 100, 'received' => 100.0, 'balance' => $balances[$i], 'last_received' => '2026-08-15'];
        }
        Cache::put(ReceivableFigures::key($this->asOn), ['computed_at' => '2026-10-01 09:45:00', 'rows' => $rows], 60);
    }

    private function rows(array $q = [])
    {
        return $this->get(route('receivable-followup.rows', $q))->assertOk()->getContent();
    }

    public function test_page_opens_with_last_month_selected()
    {
        list($m, $y) = ReceivableFigures::lastMonth();
        $html = $this->get(route('receivable-followup.index'))->assertOk()->getContent();
        $this->assertContains('Receivables Follow-up', $html);
        $this->assertRegExp('/<option value="' . $m . '" selected/', $html);
        $this->assertRegExp('/<option value="' . $y . '" selected/', $html);
    }

    public function test_rows_show_figures_sorted_by_balance_with_totals()
    {
        $html = $this->rows();
        $nos = $this->contracts->pluck('tenant_contract_no')->all();
        // balance desc: 1500 (#2), 900 (#3), 500 (#1)
        $this->assertTrue(strpos($html, $nos[1]) < strpos($html, $nos[2]) && strpos($html, $nos[2]) < strpos($html, $nos[0]));
        $this->assertContains('2,900.000', $html);           // total balance
        $this->assertContains('Figures as of 01/10/2026 09:45', $html);
    }

    public function test_filter_by_building_and_management_type()
    {
        $c = $this->contracts[0];
        $html = $this->rows(['building_id' => $c->building_id]);
        $this->assertContains($c->tenant_contract_no, $html);
        $this->assertNotContains($this->contracts[1]->tenant_contract_no, $html);

        $html = $this->rows(['management_id' => $c->management_id]);
        foreach ($this->contracts as $other) {
            $want = (int) $other->management_id === (int) $c->management_id;
            $this->assertSame($want, strpos($html, $other->tenant_contract_no) !== false, $other->tenant_contract_no);
        }
    }

    public function test_search_by_contract_no()
    {
        $html = $this->rows(['q' => $this->contracts[2]->tenant_contract_no]);
        $this->assertContains($this->contracts[2]->tenant_contract_no, $html);
        $this->assertNotContains($this->contracts[0]->tenant_contract_no, $html);
    }

    public function test_last_followup_and_overdue_promise_shown()
    {
        $c = $this->contracts[0];
        ReceivableFollowup::create(['tenant_contract_id' => $c->id, 'comment' => 'will pay', 'promised_date' => Carbon::yesterday(), 'created_by' => 1]);
        $html = $this->rows();
        $this->assertRegExp('/id="rf-promise-' . $c->id . '"[^>]*class="[^"]*rf-overdue/', $html);
        $this->assertContains(Carbon::yesterday()->format('d/m/Y'), $html);
    }

    public function test_are_user_without_buildings_sees_nothing()
    {
        $are = User::role('are')->get()->first(function ($u) {
            return $u->roles->count() === 1 && !DB::table('are_buildings')->where('user_id', $u->id)->exists();
        });
        if (!$are) {
            $this->markTestSkipped('No local ARE user without buildings.');
        }
        $this->actingAs($are);
        $html = $this->rows();
        foreach ($this->contracts as $c) {
            $this->assertNotContains($c->tenant_contract_no, $html);
        }
        $this->assertContains('No agreements with a balance', $html);
    }

    public function test_are_user_sees_only_own_buildings()
    {
        $pb = DB::table('preferred_buildings')->join('are_buildings', 'are_buildings.id', '=', 'preferred_buildings.are_building_id')
            ->whereNull('preferred_buildings.assign_to')->whereIn('preferred_buildings.building_id', $this->contracts->pluck('building_id'))
            ->first(['are_buildings.user_id', 'preferred_buildings.building_id']);
        $are = $pb ? User::find($pb->user_id) : null;
        if (!$are || $are->roles->count() !== 1 || !$are->hasRole('are')) {
            $this->markTestSkipped('No single-role ARE assigned to the sample buildings.');
        }
        $this->actingAs($are);
        $html = $this->rows();
        foreach ($this->contracts as $c) {
            $own = DB::table('preferred_buildings')->join('are_buildings', 'are_buildings.id', '=', 'preferred_buildings.are_building_id')
                ->whereNull('preferred_buildings.assign_to')->where('are_buildings.user_id', $are->id)->where('preferred_buildings.building_id', $c->building_id)->exists();
            $this->assertSame($own, strpos($html, $c->tenant_contract_no) !== false);
        }
    }

    public function test_needs_view_permission()
    {
        $user = User::all()->first(function ($u) { return !$u->can('view_receivable_followup'); });
        if (!$user) {
            $this->markTestSkipped('No local user without view_receivable_followup.');
        }
        $this->actingAs($user)->get(route('receivable-followup.index'))->assertStatus(403);
    }

    public function test_refresh_recomputes_the_month()
    {
        $this->app->bind(ReceivableFigures::class, function () {
            return new class extends ReceivableFigures {
                public function compute($asOn) { return []; }
            };
        });
        $this->postJson(route('receivable-followup.refresh'), [])->assertOk()->assertJsonStructure(['computed_at']);
        $this->assertSame([], Cache::get(ReceivableFigures::key($this->asOn))['rows']);
    }
}
