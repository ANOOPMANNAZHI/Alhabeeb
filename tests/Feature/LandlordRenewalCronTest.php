<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Modules\BackOffice\Http\Controllers\TenantRenewalController;

/**
 * The renewal cron's landlord part (08/10/2026): it set renewal status 6 ("superseded") on every
 * current contract of an approved enquiry, so a renewed contract vanished from Landlord Contracts
 * and Landlord Renewal Due minutes after approval. Now it only starts approved contracts
 * (renewal status 0 / 5) with renewal status 0, closes the contract they replace, and leaves
 * contracts under renewal (1-4) and superseded ones (6) alone.
 */
class LandlordRenewalCronTest extends TestCase
{
    use DatabaseTransactions;

    private $enquiryId;

    protected function setUp(): void
    {
        parent::setUp();
        // the vacancy part of the cron is not under test; mark it done for today so it skips
        DB::table('vacant_vacany_loss')->where('status', 1)->update(['vacant_to' => date('Y-m-d')]);
        $this->enquiryId = DB::table('landlord_contract as lc')->join('sales_enquiries as se', 'se.id', '=', 'lc.sale_enquiry_id')
            ->where('se.work_flow_processes_code', 204)->value('lc.sale_enquiry_id');
    }

    /** A landlord contract of an approved enquiry, copied from a real one. */
    private function contract($status, $renewalStatus, $from, $to)
    {
        $row = (array) DB::table('landlord_contract')->where('sale_enquiry_id', $this->enquiryId)->first();
        unset($row['id']);
        $row = array_merge($row, [
            'landlord_contract_no' => 'TST' . mt_rand(1000000, 9999999), 'landlord_contract_status' => $status,
            'landlord_renewal_termination_status' => $renewalStatus,
            'landlord_contract_valid_from_date' => $from, 'landlord_contract_valid_to_date' => $to, 'start_date' => $from,
        ]);
        return DB::table('landlord_contract')->insertGetId($row);
    }

    private function state($id)
    {
        $c = DB::table('landlord_contract')->find($id);
        return [(int) $c->landlord_contract_status, (int) $c->landlord_renewal_termination_status];
    }

    private function inLandlordContractsList($id)
    {
        return DB::table('landlord_contract')->where('id', $id)->whereIn('landlord_contract_status', [1, 2, 3])
            ->where('landlord_renewal_termination_status', '!=', 6)->exists();
    }

    private function runCron()
    {
        (new TenantRenewalController)->cronRenewalContract();
    }

    public function test_renewal_approved_after_its_start_stays_in_landlord_contracts()
    {
        $new = $this->contract(1, 0, date('Y-m-d', strtotime('-10 days')), date('Y-m-d', strtotime('+355 days')));
        $this->runCron();
        $this->assertSame([1, 0], $this->state($new));
        $this->assertTrue($this->inLandlordContractsList($new));
    }

    public function test_renewal_approved_before_its_start_becomes_active_and_replaces_the_old_contract()
    {
        $old = $this->contract(1, 6, date('Y-m-d', strtotime('-365 days')), date('Y-m-d', strtotime('-1 day')));
        $new = $this->contract(0, 5, date('Y-m-d'), date('Y-m-d', strtotime('+364 days')));
        DB::table('renewals')->insert(['old_contract_id' => $old, 'new_contract_id' => $new, 'renewal_type' => 2,
            'work_flow_processes_code' => 403, 'status' => 1, 'created_by' => 1, 'created_at' => now(), 'updated_at' => now()]);

        $this->runCron();

        $this->assertSame([1, 0], $this->state($new), 'new contract active, not superseded');
        $this->assertSame([0, 6], $this->state($old), 'old contract closed');
        $this->assertTrue($this->inLandlordContractsList($new));
    }

    public function test_contract_under_renewal_is_left_alone()
    {
        $current = $this->contract(1, 2, date('Y-m-d', strtotime('-300 days')), date('Y-m-d', strtotime('+65 days')));
        $this->runCron();
        $this->assertSame([1, 2], $this->state($current));
    }

    public function test_unapproved_duplicate_is_not_switched_on()
    {
        $draft = $this->contract(0, 3, date('Y-m-d', strtotime('-5 days')), date('Y-m-d', strtotime('+360 days')));
        $this->runCron();
        $this->assertSame([0, 3], $this->state($draft));
    }
}
