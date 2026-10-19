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
 * Al Themar Bldg (429) months run from the 21st to the 20th (January = 21 Jan - 20 Feb); other buildings use
 * calendar months. Expected figures come straight from the service report / invoice tables.
 */
class RepairMaintenanceReportTest extends TestCase
{
    use DatabaseTransactions;

    const AL_THEMAR = 429;

    public function test_al_themar_january_is_21_jan_to_20_feb()
    {
        $in  = RepairMaintenanceReport::inHouse(self::AL_THEMAR, 2026);
        $sub = RepairMaintenanceReport::subContract(self::AL_THEMAR, 2026);

        $this->assertSameReports($this->expectedReports(self::AL_THEMAR, '2026-01-21', '2026-02-21'), $in[1]);
        $this->assertSameLines($this->expectedInvoiceLines(self::AL_THEMAR, '2026-01-21', '2026-02-21'), isset($sub[1]) ? $sub[1] : []);
        foreach ($in[1] as $r) {
            $this->assertTrue($r->sr_date >= '2026-01-21' && $r->sr_date <= '2026-02-20', $r->sr_date);
        }
        // 21 Aug - 20 Sep is "August"; 1-20 Jan 2026 belongs to December 2025 and is not in the 2026 workbook
        $this->assertSameReports($this->expectedReports(self::AL_THEMAR, '2026-08-21', '2026-09-21'), $in[8]);
        $this->assertSameLines($this->expectedInvoiceLines(self::AL_THEMAR, '2026-08-21', '2026-09-21'), isset($sub[8]) ? $sub[8] : []);
        $all = 0;
        foreach ($in as $m) { $all += count($m); }
        $this->assertSame(count($this->expectedReports(self::AL_THEMAR, '2026-01-21', '2027-01-21')), $all);

        $first = $in[1][0];
        $this->assertNotEmpty($first->complaint_no);
        $this->assertNotEmpty($first->technician);
        $this->assertNotEmpty($first->category);
        $this->assertEquals((float) $first->amount, round((float) $first->material + (float) $first->labour, 3));
        $this->assertSame('21.01.2026 to 20.02.2026', RepairMaintenanceReport::periodLabel(2026, 1, 21));
        $this->assertSame('21.12.2026 to 20.01.2027', RepairMaintenanceReport::periodLabel(2026, 12, 21));
        $this->assertNull(RepairMaintenanceReport::periodLabel(2026, 1, 1));
    }

    public function test_other_buildings_use_calendar_months()
    {
        $other = null;
        foreach (RepairMaintenanceReport::buildingsWithData(2026) as $b) {
            if ((int) $b->id !== self::AL_THEMAR && count($this->expectedReports($b->id, '2026-09-01', '2026-10-01')) > 0) { $other = $b->id; break; }
        }
        $this->assertNotNull($other);
        $this->assertSame(1, RepairMaintenanceReport::startDay($other));
        $this->assertSame(21, RepairMaintenanceReport::startDay(self::AL_THEMAR));
        $in  = RepairMaintenanceReport::inHouse($other, 2026);
        $sub = RepairMaintenanceReport::subContract($other, 2026);
        $this->assertSameReports($this->expectedReports($other, '2026-09-01', '2026-10-01'), $in[9]);
        $this->assertSameLines($this->expectedInvoiceLines($other, '2026-09-01', '2026-10-01'), isset($sub[9]) ? $sub[9] : []);
    }

    private function expectedReports($buildingId, $from, $to)
    {
        return DB::select("SELECT cs.service_report_no, (SELECT COALESCE(SUM(total_charge), 0) FROM complaint_service_report_inv ci
                WHERE ci.complaint_service_report_id = cs.id) AS total
            FROM complaint_service_report cs
            WHERE cs.created_at >= ? AND cs.created_at < ? AND cs.id IN (
                SELECT csr.complaint_service_report_id FROM complaint_service_report_checklist csr
                JOIN complaint_checklists cc ON cc.id = csr.checklist_id
                JOIN complaint_enquiries ce ON ce.id = cc.complaint_enquiries_id WHERE ce.building_id = ?)", [$from, $to, $buildingId]);
    }

    private function expectedInvoiceLines($buildingId, $from, $to)
    {
        return DB::select("SELECT mid.id, CAST(NULLIF(TRIM(mid.debit_amt), '') AS numeric) AS amount
            FROM maintenance_invoices mi JOIN maintenance_invoice_details mid ON mid.maintenance_invoice_id = mi.id
            WHERE mi.deleted_at IS NULL AND mid.building_id = ? AND mi.maintenance_invoice_date >= ? AND mi.maintenance_invoice_date < ?
              AND mid.service_report_id IS NULL
              AND NOT EXISTS (SELECT 1 FROM complaint_service_report cs WHERE TRIM(mid.invoice_desc) LIKE 'SERV%'
                              AND cs.service_report_no = split_part(TRIM(mid.invoice_desc), ' ', 1))", [$buildingId, $from, $to]);
    }

    private function assertSameReports(array $expected, array $rows)
    {
        $want = [];
        foreach ($expected as $e) { $want[$e->service_report_no] = round((float) $e->total, 3); }
        $got = [];
        foreach ($rows as $r) { $got[$r->service_report_no] = round((float) $r->amount, 3); }
        ksort($want); ksort($got);
        $this->assertSame($want, $got);
    }

    private function assertSameLines(array $expected, array $rows)
    {
        $want = [];
        foreach ($expected as $e) { $want[(int) $e->id] = round((float) $e->amount, 3); }
        $got = [];
        foreach ($rows as $r) { $got[(int) $r->line_id] = round((float) $r->amount, 3); }
        ksort($want); ksort($got);
        $this->assertSame($want, $got);
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
        // 22nd-to-21st: the running period is the one that started on or before today
        $shifted = strtotime('-20 days', strtotime(date('Y-m-d')));
        $this->assertSame(range(1, (int) date('n', $shifted)), RepairMaintenanceReport::months((int) date('Y', $shifted), 21));
        $this->assertSame(range(1, 12), RepairMaintenanceReport::months(2025, 21));
        $this->assertSame('Emergency', RepairMaintenanceReport::complaintType(1));
        $this->assertSame('Normal', RepairMaintenanceReport::complaintType(0));
    }

    public function test_workbook_has_a_sheet_per_month_with_totals()
    {
        $months = RepairMaintenanceReport::months(2026, 21);
        $in  = RepairMaintenanceReport::inHouse(self::AL_THEMAR, 2026);
        $sub = RepairMaintenanceReport::subContract(self::AL_THEMAR, 2026);
        $export = new RepairMaintenanceReportExport('Al Themar Bldg', 2026, $months, $in, $sub, 21);
        $path = storage_path('app/temp/rmr_test_' . uniqid() . '.xlsx');
        @mkdir(dirname($path), 0755, true);
        file_put_contents($path, \Excel::raw($export, \Maatwebsite\Excel\Excel::XLSX));

        $book = IOFactory::load($path);
        $this->assertSame(count($months), $book->getSheetCount());
        $jan = $book->getSheet(0);
        $this->assertSame('Jan 2026', $jan->getTitle());
        $this->assertStringEndsWith('(21.01.2026 to 20.02.2026)', $jan->getCell('B1')->getValue());
        $this->assertSame('Complaint Number', $jan->getCell('B3')->getValue());
        $this->assertSame('Total Amount RO', $jan->getCell('L3')->getValue());
        $this->assertNull($jan->getCell('M3')->getValue());
        $this->assertTrue(is_numeric($jan->getCell('B4')->getValue()));
        $this->assertSame(substr($in[1][0]->complaint_no, 3), (string) $jan->getCell('B4')->getValue());

        // reference layout: widths, heights, yellow header, bold, light orange totals
        $this->assertEquals(45.14, $jan->getColumnDimension('G')->getWidth());
        $this->assertEquals(45.75, $jan->getRowDimension(3)->getRowHeight());
        $this->assertEquals(24.75, $jan->getRowDimension(4)->getRowHeight());
        $this->assertSame('FFFF00', $jan->getStyle('B3')->getFill()->getStartColor()->getRGB());
        $this->assertTrue($jan->getStyle('G4')->getFont()->getBold());

        $inTotal = $subTotal = null;
        foreach ($jan->getRowIterator() as $row) {
            $r = $row->getRowIndex();
            if (strpos((string) $jan->getCell('J' . $r)->getValue(), '=SUM(') === 0) {
                if ($inTotal === null) { $inTotal = $r; } else { $subTotal = $r; }
            }
        }
        $this->assertSame('FBE5D6', $jan->getStyle('L' . $inTotal)->getFill()->getStartColor()->getRGB());
        $this->assertSame('Sr No', $jan->getCell('B' . ($inTotal + 2))->getValue());
        $this->assertEquals(41.25, $jan->getRowDimension($inTotal + 2)->getRowHeight());
        $sum = function ($rows, $field) { return round(array_sum(array_map(function ($r) use ($field) { return (float) $r->$field; }, $rows)), 3); };
        $this->assertEquals($sum($this->expectedReports(self::AL_THEMAR, '2026-01-21', '2026-02-21'), 'total'), round($jan->getCell('L' . $inTotal)->getCalculatedValue(), 3));
        $this->assertEquals($sum($this->expectedInvoiceLines(self::AL_THEMAR, '2026-01-21', '2026-02-21'), 'amount'), round($jan->getCell('J' . $subTotal)->getCalculatedValue(), 3));
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
