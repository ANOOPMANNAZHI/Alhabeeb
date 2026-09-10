<?php
/**
 * One-off export: all active (Approved) landlord contracts on Comprehensive-
 * managed buildings, with whatever management fee data is currently saved.
 * Usage: php generate_comprehensive_management_fee.php
 */
error_reporting(E_ALL & ~E_DEPRECATED);
require 'C:/laragon/www/plms_backup/vendor/autoload.php';
$app = require 'C:/laragon/www/plms_backup/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$outputPath = __DIR__ . '/Comprehensive_Landlord_Management_Fee.xlsx';

$rows = DB::select("
    SELECT
        v.vendor_name AS landlord,
        lc.landlord_contract_no AS agreement_no,
        lc.created_at AS agreement_date,
        mt.management_types_name AS management_type,
        lc.management_method,
        lc.landlord_contract_management_fee AS fee_value
    FROM landlord_contract lc
    LEFT JOIN vendors v ON v.id = lc.vendor_id
    LEFT JOIN management_types mt ON mt.id = lc.management_id
    WHERE lc.management_id = 1
      AND lc.landlord_contract_status = 1
    ORDER BY v.vendor_name ASC
");

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Comprehensive Mgmt Fee');

// ---- Title block ----
$sheet->mergeCells('A1:F1');
$sheet->setCellValue('A1', 'Comprehensive Buildings - Management Fee (Current Data)');
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->setColor(new Color('FFFFFFFF'));
$sheet->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('1F4E78');
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
$sheet->getRowDimension(1)->setRowHeight(26);

$sheet->mergeCells('A2:F2');
$sheet->setCellValue('A2', 'Active (Approved) contracts only  |  Generated: ' . date('d-M-Y'));
$sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(9)->setColor(new Color('FF666666'));
$sheet->getRowDimension(2)->setRowHeight(16);

// ---- Header row ----
$headers = ['Landlord', 'Agreement No', 'Agreement Date', 'Management Type', 'Management Fee Type', 'Management Fee Value'];
$headerRow = 4;
$col = 'A';
foreach ($headers as $h) {
    $sheet->setCellValue($col . $headerRow, $h);
    $col++;
}
$sheet->getStyle('A' . $headerRow . ':F' . $headerRow)->getFont()->setBold(true)->setColor(new Color('FFFFFFFF'));
$sheet->getStyle('A' . $headerRow . ':F' . $headerRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('045DC2');
$sheet->getStyle('A' . $headerRow . ':F' . $headerRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getRowDimension($headerRow)->setRowHeight(20);

// ---- Data rows ----
$r = $headerRow + 1;
foreach ($rows as $row) {
    $method = $row->management_method;
    $feeValue = $row->fee_value;

    if ($method == 1) {
        $feeType = 'Percentage';
        $feeDisplay = ($feeValue !== null) ? number_format((float)$feeValue, 2) . '%' : '-';
    } elseif ($method == 2) {
        $feeType = 'Amount';
        $feeDisplay = ($feeValue !== null) ? number_format((float)$feeValue, 3) : '-';
    } else {
        $feeType = '-';
        $feeDisplay = '-';
    }

    $sheet->setCellValue('A' . $r, $row->landlord ?: '-');
    $sheet->setCellValue('B' . $r, $row->agreement_no ?: '-');
    $sheet->setCellValue('C' . $r, $row->agreement_date ? date('d/m/Y', strtotime($row->agreement_date)) : '-');
    $sheet->setCellValue('D' . $r, $row->management_type ?: '-');
    $sheet->setCellValue('E' . $r, $feeType);
    $sheet->setCellValue('F' . $r, $feeDisplay);

    if ($r % 2 == 0) {
        $sheet->getStyle('A' . $r . ':F' . $r)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('DCEBF5');
    }
    $r++;
}

foreach (['A', 'B', 'C', 'D', 'E', 'F'] as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

$writer = new Xlsx($spreadsheet);
$writer->save($outputPath);

echo "Saved: $outputPath (" . count($rows) . " active Comprehensive contracts)\n";
$noFee = 0;
foreach ($rows as $row) {
    if ($row->management_method === null && (float)$row->fee_value === 0.0) $noFee++;
}
echo "Rows with no management fee configured (method blank, value 0): $noFee\n";
