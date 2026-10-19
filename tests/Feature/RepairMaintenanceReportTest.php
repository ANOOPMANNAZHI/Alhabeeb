<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Modules\Maintenance\Exports\RepairMaintenanceReportExport;
use Modules\Maintenance\Http\Controllers\RepairMaintenanceReportController;
use Modules\Maintenance\Services\RepairMaintenanceReport;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Repair & Maintenance Report: per building, one sheet per month, In-house (service reports by service report
 * date, charges from the service report) and Sub-contract (vendor invoice lines by invoice date).
 * Al Themar Bldg (429), Sept 2026: 16 service reports = 137.600, 3 vendor invoice lines = 871.500.
 */
class RepairMaintenanceReportTest extends TestCase
{
    use DatabaseTransactions;

    const AL_THEMAR = 429;

    public function test_in_house_is_service_reports_by_service_report_date()
    {
        $in  = RepairMaintenanceReport::inHouse(self::AL_THEMAR, 2026);
        $sub = RepairMaintenanceReport::subContract(self::AL_THEMAR, 2026);

        // straight from the service report tables
        $expected = DB::select("SELECT cs.id, cs.service_report_no, (SELECT COALESCE(SUM(total_charge), 0) FROM complaint_service_report_inv ci
                WHERE ci.complaint_service_report_id = cs.id) AS total
            FROM complaint_service_report cs
            WHERE cs.created_at >= '2026-09-01' AND cs.created_at < '2026-10-01' AND cs.id IN (
                SELECT csr.complaint_service_report_id FROM complaint_service_report_checklist csr
                JOIN complaint_checklists cc ON cc.id = csr.checklist_id
                JOIN complaint_enquiries ce ON ce.id = cc.complaint_enquiries_id WHERE ce.building_id = 429)");
        $this->assertCount(16, $expected); // 3 of them have no charges (0.000)
        $this->assertCount(16, $in[9]);
        $this->assertEquals(137.6, round(array_sum(array_map(function ($r) { return (float) $r->amount; }, $in[9])), 3));
        $byNo = [];
        foreach ($expected as $e) { $byNo[$e->service_report_no] = (float) $e->total; }
        foreach ($in[9] as $r) {
            $this->assertArrayHasKey($r->service_report_no, $byNo);
            $this->assertEquals($byNo[$r->service_report_no], (float) $r->amount);
            $this->assertSame('2026-09', substr($r->sr_date, 0, 7));
        }
        $this->assertCount(3, $sub[9]);
        $this->assertEquals(871.5, round(array_sum(array_map(function ($r) { return (float) $r->amount; }, $sub[9])), 3));

        $first = $in[9][0];
        $this->assertNotEmpty($first->complaint_no);
        $this->assertNotEmpty($first->technician);
        $this->assertNotEmpty($first->category);
        $this->assertEquals((float) $first->amount, round((float) $first->material + (float) $first->labour, 3));
        $vendors = array_map(function ($r) { return $r->vendor_name; }, $sub[9]);
        $this->assertContains('MUSCAT PEST CONTROL', $vendors);
    }

    public function test_invoice_lines_billing_a_service_report_are_not_sub_contract()
    {
        // VIP2600013 (20/01/2026) bills SERV41650 etc. under "SUB CONTRACTOR BILLS" without service_report_id
        $sub = RepairMaintenanceReport::subContract(self::AL_THEMAR, 2026);
        foreach ($sub as $month) {
            foreach ($month as $r) {
                $this->assertNotSame('VIP2600013', $r->invoice_no);
                $this->assertNotSame('SUB CONTRACTOR BILLS', $r->vendor_name);
            }
        }
    }

    public function test_months_stop_at_current_month_for_current_year()
    {
        $this->assertSame(range(1, 12), RepairMaintenanceReport::months(2025));
        $this->assertSame(range(1, (int) date('n')), RepairMaintenanceReport::months((int) date('Y')));
        $this->assertSame([], RepairMaintenanceReport::months((int) date('Y') + 1));
        $this->assertSame('Emergency', RepairMaintenanceReport::complaintType(1));
        $this->assertSame('Normal', RepairMaintenanceReport::complaintType(0));
    }

    public function test_workbook_has_a_sheet_per_month_with_totals()
    {
        $months = RepairMaintenanceReport::months(2026);
        $export = new RepairMaintenanceReportExport('Al Themar Bldg', 2026, $months,
            RepairMaintenanceReport::inHouse(self::AL_THEMAR, 2026), RepairMaintenanceReport::subContract(self::AL_THEMAR, 2026));
        $path = storage_path('app/temp/rmr_test_' . uniqid() . '.xlsx');
        @mkdir(dirname($path), 0755, true);
        file_put_contents($path, \Excel::raw($export, \Maatwebsite\Excel\Excel::XLSX));

        $book = IOFactory::load($path);
        $this->assertSame(count($months), $book->getSheetCount());
        $this->assertSame('Jan 2026', $book->getSheet(0)->getTitle());
        $sep = $book->getSheetByName('Sep 2026');
        $this->assertNotNull($sep);
        $this->assertSame('Complaint Number', $sep->getCell('A4')->getValue());

        $inTotal = $subTotal = null;
        foreach ($sep->getRowIterator() as $row) {
            $r = $row->getRowIndex();
            if ($sep->getCell('I' . $r)->getValue() === 'Total' || $sep->getCell('H' . $r)->getValue() === 'Total') {
                if ($inTotal === null) { $inTotal = $r; } else { $subTotal = $r; }
            }
        }
        $this->assertEquals(137.6, round($sep->getCell('L' . $inTotal)->getCalculatedValue(), 3));
        $this->assertEquals(871.5, round($sep->getCell('I' . $subTotal)->getCalculatedValue(), 3));
        $this->assertSame('Service Report No', $sep->getCell('B4')->getValue());
        $this->assertStringStartsWith('SERV', $sep->getCell('B5')->getValue());
        $this->assertSame('Sr No', $sep->getCell('A' . ($subTotal - 4))->getValue());
        @unlink($path);
    }

    public function test_file_name_and_access()
    {
        $this->assertSame('Al Themar Bldg-Repair & Maintenance 2026.xlsx', RepairMaintenanceReportController::fileName('Al Themar Bldg', 2026));
        $this->assertSame('A-B Bldg-Repair & Maintenance 2026.xlsx', RepairMaintenanceReportController::fileName('A/B Bldg', 2026));

        $this->actingAs(User::find(1))->get(route('showRepairMaintenanceReport'))->assertOk()->assertSee('Repair &amp; Maintenance Report', false);
    }

    public function test_users_without_the_permission_get_403()
    {
        $noAccess = $this->userWithout();
        $this->actingAs($noAccess)->get(route('showRepairMaintenanceReport'))->assertStatus(403);
        $this->actingAs($noAccess)->get(route('repairMaintenanceReportDownload', 'x'))->assertStatus(403);
    }

    public function test_download_token_belongs_to_its_user()
    {
        $path = storage_path('app/temp/rmr_tok_' . uniqid() . '.xlsx');
        @mkdir(dirname($path), 0755, true);
        file_put_contents($path, 'x');
        cache()->put('rmr_dl_tok1', ['path' => $path, 'name' => 'a.xlsx', 'user' => 1], now()->addMinutes(5));

        $other = $this->userWithout();
        $other->givePermissionTo(RepairMaintenanceReportController::PERMISSION);
        $this->actingAs($other)->get(route('repairMaintenanceReportDownload', 'tok1'))->assertStatus(404);
        $this->assertNotNull(cache()->get('rmr_dl_tok1'));
        cache()->put('rmr_dl_tok2', ['path' => $path, 'name' => 'a.xlsx', 'user' => $other->id], now()->addMinutes(5));
        $this->actingAs($other)->get(route('repairMaintenanceReportDownload', 'tok2'))->assertOk();
        cache()->forget('rmr_dl_tok1');
        $this->assertNull(cache()->get('rmr_dl_tok2'));
    }

    private function userWithout()
    {
        app('cache')->forget('spatie.permission.cache');
        return User::whereDoesntHave('roles', function ($q) {
            $q->whereIn('name', ['super_admin', 'maintenance_manager', 'facility_manager', 'maintenance_supervisor',
                'maintenance_coordinator', 'maintenance_executive', 'maintenance_engineer', 'maintenance_contracts_executive']);
        })->whereDoesntHave('permissions')->firstOrFail();
    }
}
