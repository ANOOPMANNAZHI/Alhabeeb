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

    /** Cached figures for the three sample contracts (as built up to the cutoff). */
    private function seed3(array $balances, array $paidTill = null)
    {
        $paidTill = $paidTill ?: ['2026-05-31', '2026-06-30', '2026-04-30'];
        $rows = [];
        foreach ($this->contracts as $i => $c) {
            $rows[$c->id] = ['due' => $balances[$i] + 100, 'received' => 100.0, 'balance' => $balances[$i],
                'last_received' => '2026-05-02', 'last_amount' => 350.0, 'paid_till' => $paidTill[$i],
                'not_paid_from' => date('Y-m-d', strtotime($paidTill[$i] . ' +1 day')), 'start' => '2025-01-01'];
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

    private function save($c, array $data)
    {
        return $this->postJson(route('receivable-followup.store', $c->id), $data + ['as_on' => $this->asOn]);
    }

    public function test_save_captures_user_time_and_balance()
    {
        $c = $this->contracts[1];
        $this->save($c, ['comment' => 'Called, will pay Sunday', 'promised_date' => Carbon::today()->addDays(3)->format('Y-m-d')])
            ->assertOk()->assertJson(['promised' => Carbon::today()->addDays(3)->format('d/m/Y'), 'overdue' => false]);
        $f = ReceivableFollowup::where('tenant_contract_id', $c->id)->latest('id')->first();
        $this->assertSame(1, (int) $f->created_by);
        $this->assertEquals(1500.0, (float) $f->balance_at_followup);
        $this->assertTrue($f->created_at->gt(Carbon::now()->subMinute()));
    }

    public function test_save_without_promised_date()
    {
        $c = $this->contracts[0];
        $this->save($c, ['comment' => 'No answer'])->assertOk()->assertJson(['promised' => null]);
        $this->assertNull(ReceivableFollowup::where('tenant_contract_id', $c->id)->latest('id')->first()->promised_date);
    }

    public function test_comment_required_and_max_1000()
    {
        $c = $this->contracts[0];
        $this->save($c, ['comment' => ''])->assertStatus(422)->assertJsonValidationErrors('comment');
        $this->save($c, ['comment' => str_repeat('x', 1001)])->assertStatus(422)->assertJsonValidationErrors('comment');
    }

    public function test_promised_date_today_ok_yesterday_rejected()
    {
        $c = $this->contracts[0];
        $this->save($c, ['comment' => 'today', 'promised_date' => Carbon::today()->format('Y-m-d')])->assertOk();
        $this->save($c, ['comment' => 'past', 'promised_date' => Carbon::yesterday()->format('Y-m-d')])
            ->assertStatus(422)->assertJsonValidationErrors('promised_date');
    }

    public function test_save_without_cached_figures_keeps_balance_null()
    {
        Cache::forget(ReceivableFigures::key($this->asOn));
        $c = $this->contracts[0];
        $this->save($c, ['comment' => 'cache expired'])->assertOk();
        $this->assertNull(ReceivableFollowup::where('tenant_contract_id', $c->id)->latest('id')->first()->balance_at_followup);
    }

    public function test_save_needs_add_permission()
    {
        // an ARE whose role keeps view but loses add (rolled back with the transaction)
        $add = DB::table('permissions')->where('name', 'add_receivable_followup')->value('id');
        $areRole = DB::table('roles')->where('name', 'are')->value('id');
        DB::table('role_has_permissions')->where('permission_id', $add)->where('role_id', $areRole)->delete();
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        // a single-role ARE and a contract in one of their own buildings, so only the permission can refuse
        $own = DB::table('preferred_buildings')->join('are_buildings', 'are_buildings.id', '=', 'preferred_buildings.are_building_id')
            ->join('tenant_contracts', 'tenant_contracts.building_id', '=', 'preferred_buildings.building_id')
            ->whereNull('preferred_buildings.assign_to')->orderBy('tenant_contracts.id', 'desc')
            ->get(['are_buildings.user_id', 'tenant_contracts.id as contract_id'])
            ->first(function ($r) { $u = User::find($r->user_id); return $u && $u->roles->count() === 1 && $u->hasRole('are'); });
        if (!$own) {
            $this->markTestSkipped('No local single-role ARE with a contract in their buildings.');
        }
        $user = User::find($own->user_id);
        $this->assertTrue($user->can('view_receivable_followup'));
        $this->assertFalse($user->can('add_receivable_followup'));
        $this->actingAs($user);
        $this->postJson(route('receivable-followup.store', $own->contract_id), ['comment' => 'x'])->assertStatus(403);
        $this->assertSame(0, ReceivableFollowup::where('tenant_contract_id', $own->contract_id)->where('comment', 'x')->count());
        $this->assertNotContains('rf-followup', $this->rows());
    }

    public function test_are_cannot_save_or_view_outside_own_buildings()
    {
        $are = User::role('are')->get()->first(function ($u) {
            return $u->roles->count() === 1 && !DB::table('are_buildings')->where('user_id', $u->id)->exists();
        });
        if (!$are) {
            $this->markTestSkipped('No local ARE user without buildings.');
        }
        $this->actingAs($are);
        $c = $this->contracts[0];
        $this->save($c, ['comment' => 'x'])->assertStatus(403);
        $this->get(route('receivable-followup.timeline', $c->id))->assertStatus(403);
    }

    public function test_form_shows_header()
    {
        $c = $this->contracts[1];
        $html = $this->get(route('receivable-followup.form', $c->id) . '?as_on=' . $this->asOn)->assertOk()->getContent();
        $this->assertContains($c->tenant_contract_no, $html);
        $this->assertContains('1,500.000', $html);
        $this->assertContains('name="comment"', $html);
        $this->assertContains('name="promised_date"', $html);
    }

    public function test_timeline_newest_first_with_status()
    {
        $c = $this->contracts[0];
        ReceivableFollowup::create(['tenant_contract_id' => $c->id, 'comment' => 'first call', 'promised_date' => '2020-01-10', 'created_by' => 1, 'created_at' => '2020-01-01 10:00:00']);
        ReceivableFollowup::create(['tenant_contract_id' => $c->id, 'comment' => 'second call', 'promised_date' => Carbon::today()->addDays(5)->format('Y-m-d'), 'created_by' => 1]);
        $html = $this->get(route('receivable-followup.timeline', $c->id))->assertOk()->getContent();
        $this->assertTrue(strpos($html, 'second call') < strpos($html, 'first call'));
        $this->assertContains('rf-badge upcoming', $html);
        $this->assertRegExp('/rf-badge (kept|broken)/', $html);
    }

    public function test_timeline_escapes_comment()
    {
        $c = $this->contracts[0];
        ReceivableFollowup::create(['tenant_contract_id' => $c->id, 'comment' => '<script>alert(1)</script>', 'created_by' => 1]);
        $html = $this->get(route('receivable-followup.timeline', $c->id))->assertOk()->getContent();
        $this->assertNotContains('<script>alert(1)</script>', $html);
        $this->assertContains('&lt;script&gt;alert(1)&lt;/script&gt;', $html);
    }

    public function test_form_shows_last_three_comments_newest_first()
    {
        $c = $this->contracts[0];
        foreach (['call one', 'call two', 'call three', 'call four'] as $i => $text) {
            ReceivableFollowup::create(['tenant_contract_id' => $c->id, 'comment' => $text, 'created_by' => 1,
                'created_at' => Carbon::now()->subDays(10 - $i)->format('Y-m-d H:i:s')]);
        }
        $html = $this->get(route('receivable-followup.form', $c->id) . '?as_on=' . $this->asOn)->assertOk()->getContent();
        $this->assertContains('Recent follow-ups', $html);
        $this->assertTrue(strpos($html, 'call four') < strpos($html, 'call three') && strpos($html, 'call three') < strpos($html, 'call two'));
        $this->assertNotContains('call one', $html);
    }

    public function test_form_without_followups_has_no_recent_list()
    {
        $html = $this->get(route('receivable-followup.form', $this->contracts[2]->id) . '?as_on=' . $this->asOn)->assertOk()->getContent();
        $this->assertNotContains('Recent follow-ups', $html);
    }

    public function test_row_opens_history_and_has_no_view_button()
    {
        $c = $this->contracts[0];
        $html = $this->rows();
        $this->assertNotContains('rf-view', $html);
        $this->assertRegExp('/<tr id="rf-row-' . $c->id . '"[^>]*class="rf-row"[^>]*data-timeline="[^"]*receivable-followup\/' . $c->id . '\/timeline\?as_on=' . $this->asOn . '"[^>]*tabindex="0"/', $html);
    }

    public function test_filters_sit_behind_a_button_with_apply_and_reset()
    {
        $html = $this->get(route('receivable-followup.index'))->assertOk()->getContent();
        $this->assertRegExp('/<button[^>]*id="rf-filter-toggle"[^>]*aria-controls="rf-filter-card"[^>]*aria-expanded="false"/', $html);
        $this->assertRegExp('/id="rf-filter-card"[^>]*hidden/', $html);
        $this->assertContains('id="rf-apply"', $html);
        $this->assertContains('id="rf-reset"', $html);
        $this->assertContains('id="rf-applied"', $html);
    }

    public function test_building_filter_is_searchable()
    {
        $html = $this->get(route('receivable-followup.index'))->assertOk()->getContent();
        // a type-to-search box drives the (hidden) building select
        $this->assertRegExp('/<input[^>]*id="rf-building-q"[^>]*role="combobox"[^>]*aria-controls="rf-building-list"[^>]*aria-expanded="false"/', $html);
        $this->assertRegExp('/<ul[^>]*id="rf-building-list"[^>]*role="listbox"[^>]*hidden/', $html);
        $this->assertRegExp('/<select id="rf-building"[^>]*hidden/', $html);
    }

    public function test_every_agreement_owing_at_the_cutoff_is_listed()
    {
        // paid till very different dates: no Paid Till window any more, owing at the cutoff is enough
        $this->seed3([500.0, 1500.0, 900.0], ['2025-01-31', '2026-06-30', '2026-04-15']);
        $html = $this->rows();
        foreach ($this->contracts as $c) {
            $this->assertContains($c->tenant_contract_no, $html);
        }
    }

    public function test_row_says_not_paid_from_and_last_payment()
    {
        $html = $this->rows();
        $c = $this->contracts[1];   // paid till 30/06/2026
        $this->assertRegExp('/id="rf-row-' . $c->id . '".*?Not paid from.*?01\/07\/2026.*?paid till 30\/06\/2026/s', $this->rowHtml($html, $c->id) ?: $html);
        $this->assertContains('02/05/2026', $this->rowHtml($html, $c->id));
        $this->assertContains('350.000', $this->rowHtml($html, $c->id));
        $this->assertContains('Not Paid From', $html);
        $this->assertContains('Last Payment', $html);
    }

    public function test_never_paid_row_says_not_paid_from_its_first_month()
    {
        $c = $this->contracts[2];
        $rows = Cache::get(ReceivableFigures::key($this->asOn));
        $rows['rows'][$c->id] = ['due' => 2800.0, 'received' => 0.0, 'balance' => 2800.0, 'not_paid_from' => '2026-01-01',
            'last_received' => null, 'last_amount' => null, 'paid_till' => null, 'start' => '2026-01-01'];
        Cache::put(ReceivableFigures::key($this->asOn), $rows, 60);
        $row = $this->rowHtml($this->rows(), $c->id);
        $this->assertRegExp('/Not paid from.*?01\/01\/2026.*?never paid/s', $row);

        $html = $this->get(route('receivable-followup.form', $c->id) . '?as_on=' . $this->asOn)->assertOk()->getContent();
        $this->assertContains('01/01/2026 (never paid)', $html);
    }

    public function test_summary_explains_the_cutoff()
    {
        list($m, $y) = ReceivableFigures::lastMonth();
        $html = $this->rows();
        $this->assertContains('Dues up to <strong>' . Carbon::parse($this->asOn)->format('d/m/Y') . '</strong>', $html);
        $this->assertContains('(' . Carbon::create($y, $m, 1)->format('F Y') . ' − 2 months)', $html);
        $this->assertContains('Payments up to <strong>' . Carbon::today()->format('d/m/Y') . '</strong>', $html);
    }

    public function test_popup_header_says_not_paid_from_and_last_payment()
    {
        $c = $this->contracts[1];
        $html = $this->get(route('receivable-followup.form', $c->id) . '?as_on=' . $this->asOn)->assertOk()->getContent();
        $this->assertContains('Not paid from', $html);
        $this->assertContains('01/07/2026', $html);
        $this->assertContains('02/05/2026 · 350.000', $html);
    }

    public function test_paid_till_after_not_paid_from_reads_short_paid()
    {
        $c = $this->contracts[0];
        $cache = Cache::get(ReceivableFigures::key($this->asOn));
        $cache['rows'][$c->id]['not_paid_from'] = '2026-03-01';
        $cache['rows'][$c->id]['paid_till'] = '2026-06-30';
        Cache::put(ReceivableFigures::key($this->asOn), $cache, 60);
        $row = $this->rowHtml($this->rows(), $c->id);
        $this->assertContains('short paid', $row);
        $this->assertContains('receipts to 30/06/2026', $row);
        $this->assertNotContains('paid till', $row);

        $html = $this->get(route('receivable-followup.form', $c->id) . '?as_on=' . $this->asOn)->assertOk()->getContent();
        $this->assertContains('short paid · receipts to 30/06/2026', $html);
    }

    public function test_sort_by_not_paid_from()
    {
        $this->seed3([500.0, 1500.0, 900.0], ['2026-06-30', '2025-01-31', '2026-01-31']);
        $html = $this->rows(['sort' => 'not_paid_from', 'direction' => 'asc']);   // longest unpaid first
        $no = $this->contracts->pluck('tenant_contract_no')->all();
        $this->assertTrue(strpos($html, $no[1]) < strpos($html, $no[2]) && strpos($html, $no[2]) < strpos($html, $no[0]));
    }

    /** The <tr> of one contract, for assertions that must stay inside that row. */
    private function rowHtml($html, $id)
    {
        return preg_match('/<tr id="rf-row-' . $id . '".*?<\/tr>/s', $html, $m) ? $m[0] : '';
    }

    public function test_timeline_empty_state()
    {
        $html = $this->get(route('receivable-followup.timeline', $this->contracts[2]->id))->assertOk()->getContent();
        $this->assertContains('No follow-ups yet.', $html);
    }

    public function test_rows_answer_busy_while_the_month_is_being_calculated()
    {
        Cache::forget(ReceivableFigures::key($this->asOn));
        Cache::put(ReceivableFigures::key($this->asOn) . ':busy', 1, 2);
        $this->get(route('receivable-followup.rows'))->assertStatus(202)->assertJson(['busy' => true]);
        $this->postJson(route('receivable-followup.refresh'), [])->assertStatus(202)->assertJson(['busy' => true]);
    }

    public function test_refresh_is_rate_limited()
    {
        $this->app->bind(ReceivableFigures::class, function () {
            return new class extends ReceivableFigures {
                public function compute($asOn) { return []; }
            };
        });
        for ($i = 0; $i < 5; $i++) {
            $this->postJson(route('receivable-followup.refresh'), [])->assertOk();
        }
        $this->postJson(route('receivable-followup.refresh'), [])->assertStatus(429);
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
