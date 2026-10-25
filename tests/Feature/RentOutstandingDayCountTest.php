<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Modules\BackOffice\Services\RentOutstanding;

/** rent-outstanding:apply-day-count on the local live copy (TAG2602074: 01/09 -> 15/09, rent 240). */
class RentOutstandingDayCountTest extends TestCase
{
    use DatabaseTransactions;

    private function contract()
    {
        $c = DB::table('tenant_contracts')->where('tenant_contract_no', 'TAG2602074')->first(['id', 'tenant_contract_os']);
        if (!$c) {
            $this->markTestSkipped('TAG2602074 is not in this database');
        }
        return $c;
    }

    private function duesLine($contractId)
    {
        $dues = DB::table('termination_dues')->where('tenant_contract_id', $contractId)->first();
        return $dues ? DB::table('termination_dues_lines')->where('termination_dues_id', $dues->id)
            ->where('category', 'rent')->where('source_type', 'computed')->first() : null;
    }

    public function test_dry_run_lists_the_change_and_writes_nothing()
    {
        $c = $this->contract();
        Artisan::call('rent-outstanding:apply-day-count', ['--contract' => 'TAG2602074']);
        $out = Artisan::output();
        $this->assertStringContainsString('TAG2602074', $out);
        $this->assertStringContainsString('Dry run', $out);
        $this->assertSame((string) $c->tenant_contract_os, (string) DB::table('tenant_contracts')->where('id', $c->id)->value('tenant_contract_os'));
        $this->assertSame(0, DB::table('rent_outstanding_day_count_corrections')->where('tenant_contract_id', $c->id)->count());
    }

    public function test_apply_recalculates_with_the_30_day_rule_and_can_be_rerun()
    {
        $c = $this->contract();
        $paid = RentOutstanding::paidForContract($c->id);
        Artisan::call('rent-outstanding:apply-day-count', ['--contract' => 'TAG2602074', '--apply' => true]);

        // 15 days x 240 / 30 = 120, less what was paid
        $this->assertEquals(round(120 - $paid, 3), (float) DB::table('tenant_contracts')->where('id', $c->id)->value('tenant_contract_os'), '', 0.0005);
        $line = $this->duesLine($c->id);
        if ($line) {
            $this->assertLessThanOrEqual(120.0, (float) $line->amount);
        }
        $this->assertSame(1, DB::table('rent_outstanding_day_count_corrections')->where('tenant_contract_id', $c->id)->count());

        Artisan::call('rent-outstanding:apply-day-count', ['--contract' => 'TAG2602074', '--apply' => true]);
        $this->assertSame(1, DB::table('rent_outstanding_day_count_corrections')->where('tenant_contract_id', $c->id)->count());
    }

    public function test_contracts_whose_due_does_not_change_are_not_touched()
    {
        // TAG2502365 ended on 15/08 = a whole number of months: same due under both rules
        if (!DB::table('tenant_contracts')->where('tenant_contract_no', 'TAG2502365')->exists()) {
            $this->markTestSkipped('TAG2502365 is not in this database');
        }
        Artisan::call('rent-outstanding:apply-day-count', ['--contract' => 'TAG2502365']);
        $out = Artisan::output();
        $this->assertStringNotContainsString('TAG2502365 ', $out);
        $this->assertStringContainsString('Would change 0 contract(s)', $out);
    }

    public function test_terminations_before_the_cut_over_are_not_touched()
    {
        Artisan::call('rent-outstanding:apply-day-count');
        $out = Artisan::output();
        $old = DB::table('termination as t')->join('tenant_contracts as c', 'c.id', '=', 't.contract_id')
            ->where('t.termination_date', '<', '2026-08-01')->whereRaw('t.termination_date < c.tenant_contract_valid_to_date')
            ->orderBy('t.id', 'desc')->value('c.tenant_contract_no');
        if ($old) {
            $this->assertStringNotContainsString($old . ' ', $out);
        }
        $this->assertStringContainsString('Dry run', $out);
    }
}
