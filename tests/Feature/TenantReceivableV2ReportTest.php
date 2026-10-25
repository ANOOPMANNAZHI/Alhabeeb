<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Modules\BackOffice\Services\RentDues;
use Modules\BackOffice\Services\ReceivableReportRows;

class TenantReceivableV2ReportTest extends TestCase
{
    private function busyBuilding()
    {
        foreach ((new RentDues)->forDate(date('Y-m-d'), date('Y-m-d')) as $d) {
            if ($d->ledger['balance'] > 0) {
                return $d->building_name;
            }
        }
        $this->markTestSkipped('nothing owing in this database');
    }

    public function test_excel_download_for_one_building()
    {
        $this->actingAs(User::find(1));
        $res = $this->post(route('tenantReceivablesReportPdfV2'), [
            'building_name' => $this->busyBuilding(), 'end_date' => date('Y-m-d'), 'download_type' => 'excel',
        ]);
        $res->assertOk();
        $this->assertStringContainsString('tenant_receivable_v2_' . date('Y-m-d') . '.xlsx', $res->headers->get('content-disposition'));
    }

    public function test_pdf_download_for_one_building()
    {
        $this->actingAs(User::find(1));
        $res = $this->post(route('tenantReceivablesReportPdfV2'), [
            'building_name' => $this->busyBuilding(), 'end_date' => date('Y-m-d'), 'download_type' => 'pdf',
        ]);
        $res->assertOk();
        $this->assertSame('application/pdf', $res->headers->get('content-type'));
    }

    public function test_missing_end_date_goes_back_with_an_error()
    {
        $this->actingAs(User::find(1));
        $this->from(route('showtenantReceivablesReportV2'))
            ->post(route('tenantReceivablesReportPdfV2'), ['download_type' => 'excel'])
            ->assertRedirect(route('showtenantReceivablesReportV2'))
            ->assertSessionHasErrors('end_date');
    }

    public function test_report_matches_the_aging_balances()
    {
        $building = $this->busyBuilding();
        $dues = (new RentDues)->forDate(date('Y-m-d'), date('Y-m-d'), ['building_name' => $building]);
        $rows = ReceivableReportRows::build($dues, (new RentDues)->details(array_keys($dues)));
        $this->assertNotEmpty($rows);
        $owing = 0;
        foreach ($dues as $d) {
            if ($d->ledger['balance'] > 0) {
                $owing++;
            }
        }
        $this->assertCount($owing, $rows);
        foreach ($rows as $r) {
            $this->assertEquals($r->totaldue - $r->amount_received, $r->netamtdue, $r->contract_no, 0.002);
        }
    }

    public function test_details_carry_payment_mode_contacts_and_management()
    {
        $c = DB::table('tenant_contracts as tc')->join('buildings as b', 'b.id', '=', 'tc.building_id')
            ->join('tenant as t', 't.id', '=', 'tc.tenant_id')
            ->where('tc.work_flow_processes_code', 108)->where('tc.tenant_contract_payment_type', '1')
            ->whereNotNull('b.management_id')->orderBy('tc.id', 'desc')
            ->first(['tc.id', 't.tenant_contact_no']);
        $d = (new RentDues)->details([$c->id])[$c->id];
        $this->assertSame('Monthly', $d->paymentmode);
        $this->assertSame((string) $c->tenant_contact_no, (string) $d->contact_no);
        $this->assertNotEmpty($d->management_type);
    }

    public function test_report_no_longer_calls_the_old_functions()
    {
        $src = file_get_contents(base_path('Modules/BackOffice/Http/Controllers/BackOfficeReportController.php'));
        $this->assertSame(1, preg_match('/function tenantReceivablesReportPdfV2\(.*?\r?\n}\r?\n/s', $src, $m));
        $this->assertStringNotContainsString('tenantrentreceivable', $m[0]);
    }
}
