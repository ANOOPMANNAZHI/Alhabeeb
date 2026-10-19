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
 * Repair & Maintenance Report: per building, one sheet per month, In-house (service report invoice lines with
 * their complaint) and Sub-contract (other invoice lines). Al Themar Bldg (429), Sept 2026 is the reference:
 * 14 in-house lines = 221.000, 3 sub-contract lines = 871.500.
 */
class RepairMaintenanceReportTest extends TestCase
{
    use DatabaseTransactions;

    const AL_THEMAR = 429;

    public function test_sections_match_invoices_for_al_themar_september()
    {
        $in  = RepairMaintenanceReport::inHouse(self::AL_THEMAR, 2026);
        $sub = RepairMaintenanceReport::subContract(self::AL_THEMAR, 2026);

        $this->assertCount(14, $in[9]);
        $this->assertEquals(221.0, round(array_sum(array_map(function ($r) { return (float) $r->amount; }, $in[9])), 3));
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

    public function test_lines_that_lost_their_service_report_link_are_in_house()
    {
        // VIP2600013 (20/01/2026): SERV41650 roof tank cleaning 200.000, booked without service_report_id
        $in  = RepairMaintenanceReport::inHouse(self::AL_THEMAR, 2026);
        $sub = RepairMaintenanceReport::subContract(self::AL_THEMAR, 2026);
        $complaints = array_map(function ($r) { return $r->complaint_no; }, $in[1]);
        $this->assertContains('CMP44679', $complaints);
        foreach ($sub[1] as $r) {
            $this->assertNotSame('SUB CONTRACTOR BILLS', $r->vendor_name);
        }
        $total = function ($rows) { return array_sum(array_map(function ($r) { return (float) $r->amount; }, $rows)); };
        $all = (float) DB::selectOne("SELECT SUM(CAST(NULLIF(TRIM(mid.debit_amt), '') AS numeric)) s FROM maintenance_invoices mi
            JOIN maintenance_invoice_details mid ON mid.maintenance_invoice_id = mi.id
            WHERE mi.deleted_at IS NULL AND mid.building_id = ? AND mi.maintenance_invoice_date BETWEEN '2026-01-01' AND '2026-01-31'", [self::AL_THEMAR])->s;
        $this->assertEquals(round($all, 3), round($total($in[1]) + $total($sub[1]), 3));
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
            if ($sep->getCell('H' . $r)->getValue() === 'Total') {
                if ($inTotal === null) { $inTotal = $r; } else { $subTotal = $r; }
            }
        }
        $this->assertEquals(221.0, round($sep->getCell('K' . $inTotal)->getCalculatedValue(), 3));
        $this->assertEquals(871.5, round($sep->getCell('I' . $subTotal)->getCalculatedValue(), 3));
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
