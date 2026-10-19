<?php

namespace Modules\Maintenance\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Masters\Entities\Building;
use Modules\Maintenance\Exports\RepairMaintenanceReportExport;
use Modules\Maintenance\Services\RepairMaintenanceReport;
use Auth;

/**
 * Repair & Maintenance Report (Maintenance > Reports): Excel only, one workbook per building with one sheet per
 * month. Generated through a Server-Sent Events stream with a progress overlay, like Normal Management Report v2;
 * one building downloads as .xlsx, all buildings as a .zip.
 */
class RepairMaintenanceReportController extends Controller
{
    const PERMISSION = 'view_repair_maintenance_report';
    const CACHE      = 'rmr_dl_';

    public function show()
    {
        abort_unless(Auth::user()->can(self::PERMISSION), 403);
        $buildings = Building::active()->orderBy('building_name', 'asc')->get(['id', 'building_name']);
        return view('maintenance::reports.repair_maintenance_report', compact('buildings'));
    }

    public function stream(Request $request)
    {
        abort_unless(Auth::user()->can(self::PERMISSION), 403);
        $year       = (int) $request->input('year', date('Y'));
        $buildingId = (string) $request->input('building_id', 'all');
        $userId     = Auth::id();

        @ini_set('output_buffering', 'off');
        @ini_set('zlib.output_compression', false);
        while (ob_get_level()) @ob_end_clean();
        ob_implicit_flush(true);

        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache, no-store');
        header('X-Accel-Buffering: no');
        header('Content-Encoding: none');
        header('Connection: keep-alive');

        session()->save();
        ini_set('memory_limit', '1024M');
        set_time_limit(1800);

        echo ': ' . str_repeat(' ', 4096) . "\n\n";
        flush();

        $send = function (array $data) {
            echo 'data: ' . json_encode($data) . "\n\n";
            flush();
        };

        $send(['pct' => 2, 'msg' => 'Preparing…']);

        $months = RepairMaintenanceReport::months($year);
        if ($year < 2015 || $year > (int) date('Y') || count($months) === 0) {
            $send(['pct' => 100, 'done' => true, 'error' => 'Choose a year from 2015 to ' . date('Y') . '.']);
            exit;
        }

        if ($buildingId === 'all') {
            $buildings = RepairMaintenanceReport::buildingsWithData($year);
        } else {
            $b = Building::find((int) $buildingId);
            $buildings = $b ? [(object) ['id' => $b->id, 'building_name' => $b->building_name]] : [];
        }
        if (count($buildings) === 0) {
            $send(['pct' => 100, 'done' => true, 'error' => 'No maintenance invoices found for ' . $year . '.']);
            exit;
        }

        $tempDir = storage_path('app/temp/repair_maintenance_' . uniqid());
        mkdir($tempDir, 0755, true);

        $files = [];
        $used  = [];
        $total = count($buildings);
        foreach ($buildings as $i => $b) {
            $send(['pct' => 5 + (int) floor(85 * $i / $total), 'msg' => 'Building ' . ($i + 1) . ' of ' . $total . ' – ' . $b->building_name]);
            $export = new RepairMaintenanceReportExport($b->building_name, $year, $months,
                RepairMaintenanceReport::inHouse($b->id, $year), RepairMaintenanceReport::subContract($b->id, $year));
            $name = self::fileName($b->building_name, $year);
            if (isset($used[$name])) { // two buildings with the same name must not overwrite each other in the zip
                $name = substr($name, 0, -5) . ' (' . $b->id . ').xlsx';
            }
            $used[$name] = true;
            $path = $tempDir . '/' . $name;
            file_put_contents($path, \Excel::raw($export, \Maatwebsite\Excel\Excel::XLSX));
            $files[] = ['path' => $path, 'name' => $name];
        }

        if (count($files) === 1) {
            $out = $files[0];
        } else {
            $send(['pct' => 93, 'msg' => 'Creating ZIP of ' . count($files) . ' buildings…']);
            $out = ['path' => $tempDir . '/repair_maintenance_' . $year . '.zip', 'name' => 'Repair & Maintenance ' . $year . '.zip'];
            $zip = new \ZipArchive();
            if ($zip->open($out['path'], \ZipArchive::CREATE) === true) {
                foreach ($files as $f) {
                    $zip->addFile($f['path'], $f['name']);
                }
                $zip->close();
            }
            foreach ($files as $f) {
                @unlink($f['path']);
            }
        }

        $token = uniqid('rmr', true);
        cache()->put(self::CACHE . $token, $out + ['user' => $userId], now()->addMinutes(10));
        $send(['pct' => 100, 'msg' => 'Complete! Starting download…', 'done' => true, 'token' => $token]);
        exit;
    }

    public function download($token)
    {
        abort_unless(Auth::user()->can(self::PERMISSION), 403);
        $info = cache()->get(self::CACHE . $token);
        abort_if(!$info || (int) $info['user'] !== (int) Auth::id() || !file_exists($info['path']), 404, 'File not found or expired.');
        cache()->forget(self::CACHE . $token);
        return response()->download($info['path'], $info['name'])->deleteFileAfterSend(true);
    }

    /** "Al Themar Bldg-Repair & Maintenance 2026.xlsx", without characters Windows does not allow in file names. */
    public static function fileName($buildingName, $year)
    {
        $safe = trim(preg_replace('/[\\\\\/:*?"<>|]+/', '-', $buildingName));
        return $safe . '-Repair & Maintenance ' . $year . '.xlsx';
    }
}
