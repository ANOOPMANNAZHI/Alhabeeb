<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Modules\BackOffice\Http\Controllers\TenantRenewalController;

/**
 * cronRenewalContract runs every minute and on every dashboard load. Vacancy rows already brought
 * up to today are skipped, and only one run works at a time (07/10/2026: dashboard ~9s, deadlocks).
 */
class VacancyLossCronTest extends TestCase
{
    use DatabaseTransactions;

    /** An open vacancy row, copied from a real row. */
    private function vacancyRow($unitId, $vacantFrom, $vacantTo, $days)
    {
        $row = (array) DB::table('vacant_vacany_loss')->orderBy('id')->first();
        unset($row['id']);
        $row = array_merge($row, ['unit_id' => $unitId, 'status' => 1, 'vacant_from' => $vacantFrom,
            'vacant_to' => $vacantTo, 'vacant_days' => $days, 'vacany_loss' => 0, 'rent_per_month' => 300]);
        return DB::table('vacant_vacany_loss')->insertGetId($row);
    }

    /** A real unit with no open vacancy row, so the cron's per-unit update touches only the test row. */
    private function freeUnitId($offset)
    {
        return (int) DB::table('units')->whereNotIn('id', function ($q) {
            $q->select('unit_id')->from('vacant_vacany_loss')->where('status', 1)->whereNotNull('unit_id');
        })->orderBy('id')->skip($offset)->value('id');
    }

    public function test_rows_already_updated_today_are_skipped_and_others_brought_up_to_today()
    {
        $today = date('Y-m-d');
        $from = date('Y-m-d', strtotime('-40 days'));
        $stale = $this->vacancyRow($this->freeUnitId(1), $from, date('Y-m-d', strtotime('-1 day')), 39);
        $done = $this->vacancyRow($this->freeUnitId(2), $from, $today, 999);

        (new TenantRenewalController)->cronRenewalContract();

        $s = DB::table('vacant_vacany_loss')->find($stale);
        $this->assertEquals($today, $s->vacant_to);
        $this->assertEquals(41, $s->vacant_days);
        $this->assertEquals((float) rentLossCalculationWithDays(41, 300), (float) $s->vacany_loss);
        $this->assertEquals(999, DB::table('vacant_vacany_loss')->find($done)->vacant_days, 'already up to today: not touched again');
    }

    public function test_a_second_run_skips_while_one_is_running()
    {
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $id = $this->vacancyRow($this->freeUnitId(3), date('Y-m-d', strtotime('-10 days')), $yesterday, 9);

        config(['database.connections.lock_holder' => config('database.connections.' . config('database.default'))]);
        $holder = DB::connection('lock_holder');
        $holder->selectOne("SELECT pg_advisory_lock(hashtext('cronRenewalContract'))");
        try {
            (new TenantRenewalController)->cronRenewalContract();
            $this->assertEquals($yesterday, DB::table('vacant_vacany_loss')->find($id)->vacant_to, 'skipped while locked');
        } finally {
            $holder->selectOne("SELECT pg_advisory_unlock(hashtext('cronRenewalContract'))");
            $holder->disconnect();
        }

        (new TenantRenewalController)->cronRenewalContract();
        $this->assertEquals(date('Y-m-d'), DB::table('vacant_vacany_loss')->find($id)->vacant_to, 'runs once the lock is free');
    }
}
