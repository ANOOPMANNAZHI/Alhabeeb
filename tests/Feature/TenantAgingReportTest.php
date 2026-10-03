<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Modules\BackOffice\Services\RentDues;
use Modules\BackOffice\Services\TenantAgingCalculator;

class TenantAgingReportTest extends TestCase
{
    private function busyBuilding()
    {
        // an active building with at least one contract owing today
        $dues = (new RentDues)->forDate(date('Y-m-d'), date('Y-m-d'));
        foreach ($dues as $d) {
            if ($d->ledger['balance'] > 0) {
                return $d->building_name;
            }
        }
        $this->markTestSkipped('nothing owing in this database');
    }

    public function test_excel_download_for_one_building()
    {
        $this->actingAs(User::find(1));
        $res = $this->post(route('tenantAgingReportPdf'), [
            'building_name' => $this->busyBuilding(), 'end_date' => date('Y-m-d'), 'download_type' => 'excel',
        ]);
        $res->assertOk();
        $this->assertContains('tenant_aging_' . date('Y-m-d') . '.xlsx', $res->headers->get('content-disposition'));
    }

    public function test_pdf_download_for_one_building()
    {
        $this->actingAs(User::find(1));
        $res = $this->post(route('tenantAgingReportPdf'), [
            'building_name' => $this->busyBuilding(), 'end_date' => date('Y-m-d'), 'download_type' => 'pdf',
        ]);
        $res->assertOk();
        $this->assertSame('application/pdf', $res->headers->get('content-type'));
    }

    public function test_buckets_add_up_to_each_balance()
    {
        $building = $this->busyBuilding();
        $rows = TenantAgingCalculator::rows((new RentDues)->forDate(date('Y-m-d'), date('Y-m-d'), ['building_name' => $building]), date('Y-m-d'));
        $this->assertNotEmpty($rows);
        foreach ($rows as $r) {
            $this->assertEquals($r->netamtdue, array_sum($r->aging), $r->contract_no, 0.002);
        }
    }

    public function test_missing_end_date_goes_back_with_an_error()
    {
        $this->actingAs(User::find(1));
        $res = $this->from(route('showTenantAgingReport'))->post(route('tenantAgingReportPdf'), ['download_type' => 'pdf']);
        $res->assertSessionHasErrors('end_date');
        $res->assertRedirect(route('showTenantAgingReport'));
    }

    public function test_report_no_longer_calls_the_old_functions()
    {
        $src = file_get_contents(base_path('Modules/BackOffice/Http/Controllers/BackOfficeReportController.php'));
        preg_match('/function tenantAgingReportPdf\(.*?\r?\n}\r?\n/s', $src, $m);
        $this->assertNotContains('tenantrentreceivable', $m[0]);
    }
}
