<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;

/**
 * PDC grid Save: a cheque bounced through the Cheque Bounce popup is locked (pdc_is_saved = 2).
 * Saving the grid must not create a copy of it as a new open PDC, nor change it (07/10/2026,
 * TAG2503810 cheque 2100094 came back after every delete).
 */
class PdcGridLockedBounceTest extends TestCase
{
    use DatabaseTransactions;

    /** The grid's form fields for every PDC of the contract, as the page submits them. */
    private function gridForm($contractId)
    {
        $rows = DB::table('pdc')->where('tenant_contract_id', $contractId)->orderBy('id')->get();
        $f = ['tenantContract' => $contractId, 'paymentTerm' => 1, 'existRow' => count($rows), 'deleteRow' => '',
              'pdc_transaction_date' => optional($rows->first())->pdc_transaction_date];
        foreach ($rows as $i => $p) {
            $f += [
                "pdc_check_id_$i" => $p->id, "pdc_is_saved_$i" => $p->pdc_is_saved, "pdc_check_no_$i" => $p->pdc_check_no,
                "pdc_check_date_$i" => substr($p->pdc_check_date, 0, 10), "pdc_amt_$i" => $p->pdc_amt, "pdc_stage_$i" => $p->pdc_stage,
                "pdc_type_$i" => $p->pdc_type, "bank_id_$i" => $p->bank_id, "pdc_account_no_$i" => $p->pdc_account_no,
                "pdc_recieve_date_$i" => substr((string) $p->pdc_recieve_date, 0, 10), "pdc_from_date_$i" => $p->pdc_from_date,
                "pdc_to_date_$i" => $p->pdc_to_date, "pdc_deposit_date_$i" => $p->pdc_deposit_date, "pdc_clear_date_$i" => $p->pdc_clear_date,
                "pdc_cancel_date_$i" => $p->pdc_cancel_date, "pdc_reference_$i" => '', "pdc_bounce_reason_$i" => $p->pdc_bounce_reason,
                "pdc_cheque_acknowledge_$i" => $p->pdc_cheque_acknowledge, "pdc_remark_$i" => $p->pdc_remark,
            ];
            if ((int) $p->pdc_cancel_reason > 0) {
                $f["pdc_cancel_reason_$i"] = $p->pdc_cancel_reason;
            }
        }
        return $f;
    }

    private function lockedCheque()
    {
        $p = DB::table('pdc as p')->join('tenant_contracts as tc', 'tc.id', '=', 'p.tenant_contract_id')
            ->join('buildings as b', 'b.id', '=', 'tc.building_id')->where('b.building_name', 'not ilike', '%in house%')
            ->where('p.pdc_is_saved', 2)->where('p.pdc_cancel_reason', '1')->orderBy('p.id', 'desc')->first(['p.*']);
        if (!$p) {
            $this->markTestSkipped('no locked bounced cheque');
        }
        return $p;
    }

    public function test_saving_the_grid_does_not_copy_a_locked_bounced_cheque()
    {
        $locked = $this->lockedCheque();
        $this->actingAs(User::find(1));
        $before = DB::table('pdc')->where('tenant_contract_id', $locked->tenant_contract_id)->count();

        $this->post(route('pdc.store'), $this->gridForm($locked->tenant_contract_id))->assertRedirect();

        $this->assertSame($before, DB::table('pdc')->where('tenant_contract_id', $locked->tenant_contract_id)->count(), 'no new rows');
        $after = DB::table('pdc')->find($locked->id);
        foreach (['pdc_is_saved', 'pdc_cancel_reason', 'pdc_cancel_date', 'pdc_bounce_reason', 'pdc_remark', 'created_by'] as $col) {
            $this->assertEquals($locked->$col, $after->$col, $col);
        }
    }

    public function test_deleting_a_row_next_to_a_locked_bounced_cheque_really_deletes_it()
    {
        $locked = $this->lockedCheque();
        $this->actingAs(User::find(1));
        // a redeposit of the locked cheque, then deleted from the grid
        $copy = (array) $locked;
        unset($copy['id']);
        $copyId = DB::table('pdc')->insertGetId(array_merge($copy, [
            'pdc_is_saved' => 1, 'pdc_cancel_reason' => '0', 'pdc_cancel_date' => null, 'pdc_bounce_reason' => null,
            'pdc_remark' => 'Redeposit', 'created_at' => now(), 'updated_at' => now(),
        ]));
        $form = $this->gridForm($locked->tenant_contract_id);
        $before = DB::table('pdc')->where('tenant_contract_id', $locked->tenant_contract_id)->count();
        // drop the copy's fields, as the − button removes its table row
        $i = array_search($copyId, array_filter($form, function ($v, $k) { return strpos($k, 'pdc_check_id_') === 0; }, ARRAY_FILTER_USE_BOTH));
        $n = substr($i, strlen('pdc_check_id_'));
        foreach (array_keys($form) as $k) {
            if (preg_match('/_' . $n . '$/', $k)) {
                unset($form[$k]);
            }
        }
        $form['deleteRow'] = $copyId . ',';

        $this->post(route('pdc.store'), $form)->assertRedirect();

        $this->assertNull(DB::table('pdc')->find($copyId), 'deleted');
        $this->assertSame($before - 1, DB::table('pdc')->where('tenant_contract_id', $locked->tenant_contract_id)->count(), 'nothing re-created');
    }
}
