<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Modules\BackOffice\Services\FollowupResults;

/**
 * Follow-up Results: payments within 30 days after a follow-up, or until the next follow-up on
 * the agreement; same-day receipts only when entered after the follow-up; PDC receipts apart
 * (user, 11/10/2026). Dates are in 2030 so real data never falls in the windows.
 */
class FollowupResultsTest extends TestCase
{
    use DatabaseTransactions;

    private $n = 0;

    private function followup($contract, $user, $at, $balance)
    {
        return DB::table('receivable_followups')->insertGetId([
            'tenant_contract_id' => $contract, 'comment' => 'test', 'balance_at_followup' => $balance,
            'created_by' => $user, 'created_at' => $at, 'updated_at' => $at,
        ]);
    }

    private function receipt($contract, $date, $createdAt, $amount, $pdc = false, $status = 3, $approval = 6)
    {
        $no = 'TFR' . (++$this->n) . uniqid();
        DB::table('receipts_generation')->insert([
            'tenant_contract_id' => $contract, 'receipts_generation_payment_method' => $pdc ? 1 : 2,
            'receipts_generation_receipt_no' => $no, 'receipts_generation_receipt_date' => $date,
            'receipts_generation_amt' => $amount, 'receipts_generation_type' => 0, 'receipts_generation_index' => 0,
            'receipts_generation_status' => $status, 'receipts_generation_approval_status' => $approval,
            'created_by' => 1, 'created_at' => $createdAt, 'updated_at' => $createdAt,
        ]);
        if ($pdc) {
            DB::table('pdc')->insert([
                'tenant_contract_id' => $contract, 'pdc_transaction_no' => 'T' . $no, 'pdc_transaction_date' => $date,
                'pdc_check_date' => $date, 'pdc_recieve_date' => $date, 'pdc_period' => 1, 'bank_id' => 4, 'pdc_stage' => 1,
                'pdc_amt' => $amount, 'pdc_is_posted' => 1, 'pdc_receipt_no' => $no, 'created_by' => 1,
            ]);
        }
        return $no;
    }

    private function setUpData()
    {
        $c = DB::table('tenant_contracts')->orderBy('id', 'desc')->limit(2)->pluck('id')->all();
        $u = DB::table('users')->where('id', '<>', 1)->orderBy('id')->value('id');
        if (count($c) < 2 || !$u) {
            $this->markTestSkipped('needs two contracts and two users');
        }
        list($a, $b) = $c;
        $f1 = $this->followup($a, 1, '2030-01-05 10:00:00', 500);
        $this->receipt($a, '2030-01-05', '2030-01-05 09:00:00', 50);           // same day, entered before: not counted
        $this->receipt($a, '2030-01-10', '2030-01-10 12:00:00', 300);          // counted for F1
        $this->receipt($a, '2030-01-12', '2030-01-12 12:00:00', 100, true);    // PDC: apart
        $this->receipt($a, '2030-01-14', '2030-01-14 12:00:00', 999, false, 2); // cancelled: never
        $f2 = $this->followup($a, $u, '2030-01-20 11:00:00', 200);             // closes F1's window
        $this->receipt($a, '2030-01-25', '2030-01-25 12:00:00', 200);          // counted for F2, clears it
        $f3 = $this->followup($b, 1, '2030-01-06 09:00:00', 100);
        $this->receipt($b, '2030-02-10', '2030-02-10 12:00:00', 100);          // 35 days later: outside
        return compact('a', 'b', 'u', 'f1', 'f2', 'f3');
    }

    public function test_payments_are_credited_to_the_right_followup()
    {
        $d = $this->setUpData();
        $rows = (new FollowupResults())->followups('2030-01-01', '2030-01-31', null, '2030-03-31');
        $by = [];
        foreach ($rows as $r) {
            $by[$r->id] = $r;
        }
        $this->assertCount(3, $rows);

        $this->assertEquals(300, $by[$d['f1']]->paid);
        $this->assertEquals(100, $by[$d['f1']]->pdc);
        $this->assertSame('2030-01-20', $by[$d['f1']]->window_end);
        $this->assertTrue($by[$d['f1']]->closed_by_next);
        $this->assertFalse($by[$d['f1']]->cleared);

        $this->assertEquals(200, $by[$d['f2']]->paid);
        $this->assertTrue($by[$d['f2']]->cleared);

        $this->assertEquals(0, $by[$d['f3']]->paid);
        $this->assertSame('2030-02-05', $by[$d['f3']]->window_end);
        $this->assertFalse($by[$d['f3']]->open);
    }

    public function test_summary_per_user()
    {
        $d = $this->setUpData();
        $sum = FollowupResults::summarise((new FollowupResults())->followups('2030-01-01', '2030-01-31', null, '2030-03-31'));
        $by = [];
        foreach ($sum as $s) {
            $by[$s['user_id']] = $s;
        }
        $this->assertSame(2, $by[1]['followups']);
        $this->assertSame(2, $by[1]['agreements']);
        $this->assertSame(1, $by[1]['paid_agreements']);
        $this->assertEquals(50, $by[1]['success']);
        $this->assertEquals(300, $by[1]['paid']);
        $this->assertEquals(100, $by[1]['pdc']);
        $this->assertSame(0, $by[1]['cleared']);

        $this->assertSame(1, $by[$d['u']]['followups']);
        $this->assertEquals(200, $by[$d['u']]['paid']);
        $this->assertSame(1, $by[$d['u']]['cleared']);
        $this->assertEquals(100, $by[$d['u']]['success']);
    }

    public function test_open_window_and_user_filter()
    {
        $d = $this->setUpData();
        $rows = (new FollowupResults())->followups('2030-01-01', '2030-01-31', [$d['u']], '2030-01-25');
        $this->assertCount(1, $rows);
        $this->assertTrue($rows[0]->open);
    }

    public function test_results_page_renders_with_detail()
    {
        $d = $this->setUpData();
        $this->actingAs(User::find(1))
            ->get(route('receivable-followup.results', ['from' => '2030-01-01', 'to' => '2030-01-31', 'user' => 1]))
            ->assertStatus(200)
            ->assertSee('Follow-up Results')
            ->assertSee('Amount collected')
            ->assertSee('Part paid');
    }
}
