<?php

namespace Tests\Feature;

use Tests\TestCase;
use Modules\BackOffice\Http\Controllers\BackOfficeReportController;

/** Expense Details v2: the Expense Type filter (All / In-house / Subcontractor / General Ledger). */
class ExpenseDetailsV2FilterTest extends TestCase
{
    private function rows($type, $building = '', $head = '')
    {
        $c = new BackOfficeReportController();
        $m = new \ReflectionMethod($c, 'expenseDetailsV2Query');
        $m->setAccessible(true);
        return $m->invoke($c, '2025-01-01', '2026-12-31', $building, $type, $head);
    }

    public function test_general_ledger_head_filter_keeps_only_that_head()
    {
        $gl = $this->rows('gl');
        $heads = array_values(array_unique(array_map(function ($r) { return (string) $r->acc_code_val; }, $gl)));
        if (count($heads) < 2) {
            $this->markTestSkipped('Need GL rows on at least two heads');
        }
        $rows = $this->rows('gl', '', $heads[0]);
        $this->assertNotEmpty($rows);
        foreach ($rows as $r) {
            $this->assertSame('GL', $r->source);
            $this->assertSame($heads[0], (string) $r->acc_code_val);
        }
        $this->assertSame(count(array_filter($gl, function ($r) use ($heads) { return (string) $r->acc_code_val === $heads[0]; })), count($rows));
    }

    public function test_head_is_ignored_unless_general_ledger_is_chosen()
    {
        $gl = $this->rows('gl');
        if (!$gl) {
            $this->markTestSkipped('No GL rows');
        }
        $head = (string) $gl[0]->acc_code_val;
        $this->assertSame(count($this->rows('')), count($this->rows('', '', $head)));
    }

    public function test_page_lists_general_ledger_heads_in_a_hidden_filter()
    {
        $this->actingAs(\App\User::find(1));
        $html = $this->get(route('showExpenseDetailsReportV2'))->assertOk()->getContent();
        $this->assertMatchesRegularExpression('/<div[^>]*id="gl_head_box"[^>]*hidden/', $html);
        $this->assertStringContainsString('<select class="form-control" id="gl_head" name="gl_head"', $html);
        $this->assertStringContainsString('<option value="">All heads</option>', $html);
        $this->assertStringContainsString('<option value="41102">41102 - MUNICIPAL TAX</option>', $html);
    }

    private function sources(array $rows)
    {
        return array_values(array_unique(array_map(function ($r) { return $r->source; }, $rows)));
    }

    public function test_general_ledger_option_returns_only_gl_rows()
    {
        $all = $this->rows('');
        if (!in_array('GL', $this->sources($all), true)) {
            $this->markTestSkipped('No GL expense rows in this period locally');
        }
        $gl = $this->rows('gl');
        $this->assertNotEmpty($gl);
        $this->assertSame(['GL'], $this->sources($gl));
        $this->assertSame(count(array_filter($all, function ($r) { return $r->source === 'GL'; })), count($gl));
    }

    public function test_general_ledger_option_with_a_building()
    {
        $glRow = null;
        foreach ($this->rows('gl') as $r) { $glRow = $r; break; }
        if (!$glRow) {
            $this->markTestSkipped('No GL rows');
        }
        $rows = $this->rows('gl', $glRow->building_name);
        $this->assertNotEmpty($rows);
        $this->assertSame(['GL'], $this->sources($rows));
        foreach ($rows as $r) {
            $this->assertSame($glRow->building_name, $r->building_name);
        }
    }

    public function test_inhouse_and_subcontractor_still_exclude_gl()
    {
        foreach (['inhouse' => 'In-house', 'subcontractor' => 'Subcontractor'] as $type => $label) {
            foreach ($this->rows($type) as $r) {
                $this->assertSame('MI', $r->source);
                $this->assertSame($label, $r->expense_type);
            }
        }
    }
}
