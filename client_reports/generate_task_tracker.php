<?php
/**
 * Regenerates Task_Progress_Tracker.xlsx from tasks_data.json in this same folder.
 * Usage: php generate_task_tracker.php
 * To add a new task: add an entry to tasks_data.json, then re-run this script.
 */
error_reporting(E_ALL & ~E_DEPRECATED);
require 'C:/laragon/www/plms_backup/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$dataPath = __DIR__ . '/tasks_data.json';
$outputPath = __DIR__ . '/Task_Progress_Tracker.xlsx';

$tasks = json_decode(file_get_contents($dataPath), true);
if (!is_array($tasks)) {
    fwrite(STDERR, "Could not read tasks_data.json\n");
    exit(1);
}

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Task Progress');

// ---- Title block ----
$sheet->mergeCells('A1:G1');
$sheet->setCellValue('A1', 'PLMS - Task Progress Tracker');
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16)->setColor(new Color('FFFFFFFF'));
$sheet->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('1F4E78');
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
$sheet->getRowDimension(1)->setRowHeight(28);

$sheet->mergeCells('A2:G2');
$sheet->setCellValue('A2', 'Prepared for Client Review  |  Last Updated: ' . date('d-M-Y'));
$sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(10)->setColor(new Color('FF666666'));
$sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// ---- Header row ----
$headerRow = 4;
$headers = ['S.No', 'Date', 'Module / Screen', 'Task / Update Description', 'Files Changed (Location)', 'Status', 'Remarks'];
$col = 'A';
foreach ($headers as $h) {
    $sheet->setCellValue($col . $headerRow, $h);
    $col++;
}
$headerRange = 'A' . $headerRow . ':G' . $headerRow;
$sheet->getStyle($headerRange)->getFont()->setBold(true)->setColor(new Color('FFFFFFFF'));
$sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('2E75B6');
$sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
$sheet->getRowDimension($headerRow)->setRowHeight(24);

// ---- Data rows ----
$row = $headerRow + 1;
$sno = 1;
foreach ($tasks as $t) {
    $filesText = implode("\n", $t['files'] ?? []);
    $sheet->setCellValue('A' . $row, $sno);
    $sheet->setCellValue('B' . $row, $t['date'] ?? '');
    $sheet->setCellValue('C' . $row, $t['module'] ?? '');
    $sheet->setCellValue('D' . $row, $t['description'] ?? '');
    $sheet->setCellValue('E' . $row, $filesText);
    $sheet->setCellValue('F' . $row, $t['status'] ?? '');
    $sheet->setCellValue('G' . $row, $t['remarks'] ?? '');
    $row++;
    $sno++;
}
$lastDataRow = max($row - 1, $headerRow + 1);

// ---- Column widths ----
$sheet->getColumnDimension('A')->setWidth(7);
$sheet->getColumnDimension('B')->setWidth(13);
$sheet->getColumnDimension('C')->setWidth(22);
$sheet->getColumnDimension('D')->setWidth(55);
$sheet->getColumnDimension('E')->setWidth(45);
$sheet->getColumnDimension('F')->setWidth(14);
$sheet->getColumnDimension('G')->setWidth(35);

if (count($tasks) > 0) {
    // ---- Wrap text + vertical align for data rows ----
    $dataRange = 'A' . ($headerRow + 1) . ':G' . $lastDataRow;
    $sheet->getStyle($dataRange)->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
    $sheet->getStyle('A' . ($headerRow + 1) . ':A' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('B' . ($headerRow + 1) . ':B' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('F' . ($headerRow + 1) . ':F' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // ---- Status color coding ----
    for ($r = $headerRow + 1; $r <= $lastDataRow; $r++) {
        $status = $sheet->getCell('F' . $r)->getValue();
        $rgb = '000000';
        $bg = 'FFFFFF';
        switch (strtolower((string) $status)) {
            case 'completed':
                $bg = 'C6EFCE'; $rgb = '006100'; break;
            case 'in progress':
                $bg = 'FFEB9C'; $rgb = '9C6500'; break;
            case 'pending':
                $bg = 'FFC7CE'; $rgb = '9C0006'; break;
            case 'on hold':
                $bg = 'D9D9D9'; $rgb = '404040'; break;
        }
        $sheet->getStyle('F' . $r)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($bg);
        $sheet->getStyle('F' . $r)->getFont()->setColor(new Color('FF' . $rgb))->setBold(true);
    }

    // ---- Zebra striping ----
    for ($r = $headerRow + 1; $r <= $lastDataRow; $r++) {
        if (($r - $headerRow) % 2 === 0) {
            $sheet->getStyle('A' . $r . ':E' . $r)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F2F7FC');
            $sheet->getStyle('G' . $r)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F2F7FC');
        }
    }
}

// ---- Borders for the whole table ----
$fullTableRange = 'A' . $headerRow . ':G' . $lastDataRow;
$sheet->getStyle($fullTableRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('BFBFBF');

// ---- Freeze header & auto filter ----
$sheet->freezePane('A' . ($headerRow + 1));
$sheet->setAutoFilter($headerRange);

$writer = new Xlsx($spreadsheet);
$writer->save($outputPath);

echo "Saved: $outputPath (" . count($tasks) . " task rows)\n";
