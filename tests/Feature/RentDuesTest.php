<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Modules\BackOffice\Services\RentDues;

class RentDuesTest extends TestCase
{
    public function test_only_approved_contracts_in_active_buildings_without_legal_cases()
    {
        $ids = array_keys((new RentDues)->forDate(date('Y-m-d'), date('Y-m-d')));
        $this->assertNotEmpty($ids);
        $bad = DB::table('tenant_contracts as tc')->join('buildings as b', 'b.id', '=', 'tc.building_id')
            ->whereIn('tc.id', $ids)
            ->where(function ($q) {
                $q->where('tc.work_flow_processes_code', '<>', 108)->orWhereNull('tc.work_flow_processes_code')
                  ->orWhere('b.building_status', '<>', '1')
                  ->orWhereExists(function ($e) { $e->select(DB::raw(1))->from('legal')->whereRaw('legal.tenant_contract_id = tc.id'); });
            })->count();
        $this->assertSame(0, $bad);
    }

    public function test_unapproved_renewal_draft_is_left_out()
    {
        $draft = DB::table('tenant_contracts')->whereNull('work_flow_processes_code')
            ->where('tenant_contract_effective_date', '<=', date('Y-m-d'))->orderBy('id', 'desc')->first();
        if (!$draft) {
            $this->markTestSkipped('no unapproved draft in this database');
        }
        $this->assertArrayNotHasKey($draft->id, (new RentDues)->forDate(date('Y-m-d'), date('Y-m-d')));
    }

    public function test_receipts_after_the_receipts_date_are_not_counted()
    {
        // a contract in an active building with rent receipts on at least two different days
        $c = DB::selectOne("
            SELECT rg.tenant_contract_id AS id, b.building_name, max(rg.receipts_generation_receipt_date)::date AS last_day
              FROM receipts_generation rg
              JOIN tenant_contracts tc ON tc.id = rg.tenant_contract_id AND tc.work_flow_processes_code = 108
              JOIN buildings b ON b.id = tc.building_id AND b.building_status = '1'
             WHERE rg.receipts_generation_type = '0' AND rg.deleted_at IS NULL
               AND NOT EXISTS (SELECT 1 FROM legal l WHERE l.tenant_contract_id = tc.id)
             GROUP BY rg.tenant_contract_id, b.building_name
            HAVING count(DISTINCT rg.receipts_generation_receipt_date::date) >= 2
             ORDER BY rg.tenant_contract_id DESC LIMIT 1");
        $upTo = date('Y-m-d', strtotime($c->last_day . ' -1 day'));
        $expected = (float) DB::table('receipts_generation')->where('tenant_contract_id', $c->id)
            ->where('receipts_generation_type', '0')->whereNull('deleted_at')
            ->whereRaw('receipts_generation_receipt_date::date <= ?', [$upTo])->sum('receipts_generation_amt');

        $row = (new RentDues)->forDate(date('Y-m-d'), $upTo, ['building_name' => $c->building_name])[$c->id];
        $this->assertEquals($expected, (float) $row->received, '', 0.0005);
        $this->assertEquals(round($expected, 3), $row->ledger['received'], '', 0.0005);
    }

    public function test_termination_dated_after_the_as_on_date_is_ignored()
    {
        $t = DB::selectOne("
            SELECT tc.id, b.building_name, max(t.termination_date)::date AS term
              FROM termination t
              JOIN tenant_contracts tc ON tc.id = t.contract_id AND tc.work_flow_processes_code = 108
              JOIN buildings b ON b.id = tc.building_id AND b.building_status = '1'
             WHERE t.work_flow_processes_code = '505'
               AND NOT EXISTS (SELECT 1 FROM legal l WHERE l.tenant_contract_id = tc.id)
             GROUP BY tc.id, b.building_name, tc.tenant_contract_effective_date
            HAVING max(t.termination_date)::date > tc.tenant_contract_effective_date::date + 40
             ORDER BY tc.id DESC LIMIT 1");
        $asOn = date('Y-m-d', strtotime($t->term . ' -1 day'));
        $dues = (new RentDues)->forDate($asOn, $asOn, ['building_name' => $t->building_name]);
        $this->assertNull($dues[$t->id]->term_date);
        $after = (new RentDues)->forDate($t->term, $t->term, ['building_name' => $t->building_name]);
        $this->assertSame($t->term, $after[$t->id]->term_date);
    }

    public function test_building_filter_keeps_only_that_building()
    {
        $name = DB::table('buildings')->where('building_status', '1')->orderBy('id')->value('building_name');
        $dues = (new RentDues)->forDate(date('Y-m-d'), date('Y-m-d'), ['building_name' => $name]);
        $this->assertNotEmpty($dues);
        foreach ($dues as $d) {
            $this->assertSame($name, $d->building_name);
        }
    }

    /** Same receipts as the termination screens (RentOutstanding): approved or posted, not cancelled. */
    public function test_cancelled_and_unapproved_receipts_are_not_counted()
    {
        $c = DB::selectOne("
            SELECT rg.tenant_contract_id AS id, b.building_name
              FROM receipts_generation rg
              JOIN tenant_contracts tc ON tc.id = rg.tenant_contract_id AND tc.work_flow_processes_code = 108
              JOIN buildings b ON b.id = tc.building_id AND b.building_status = '1'
             WHERE rg.receipts_generation_type = '0' AND rg.deleted_at IS NULL AND rg.receipts_generation_status = 2
               AND NOT EXISTS (SELECT 1 FROM legal l WHERE l.tenant_contract_id = tc.id)
             ORDER BY rg.id DESC LIMIT 1");
        if (!$c) {
            $this->markTestSkipped('no cancelled rent receipt in this database');
        }
        $expected = \Modules\BackOffice\Services\RentOutstanding::paidForContract($c->id);
        $row = (new RentDues)->forDate(date('Y-m-d'), date('Y-m-d'), ['building_name' => $c->building_name])[$c->id];
        $this->assertEquals($expected, (float) $row->received, 'contract ' . $c->id, 0.0005);
    }

    public function test_ledgers_keep_unpaid_lines_that_add_up_to_the_balance()
    {
        $dues = (new RentDues)->forDate(date('Y-m-d'), date('Y-m-d'));
        $this->assertNotEmpty($dues);
        foreach ($dues as $d) {
            $sum = 0.0;
            foreach ($d->ledger['lines'] as $l) {
                $this->assertGreaterThan(0, $l['unpaid']);
                $sum += $l['unpaid'];
            }
            $this->assertEquals($d->ledger['balance'], $sum, 'contract ' . $d->id, 0.002);
        }
    }
}
