<?php

namespace Modules\BackOffice\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class NormalManagementConsolidateSheet implements FromArray, ShouldAutoSize, WithEvents, WithTitle
{
    private static $monthCols = [
        1 => 'JAN', 2 => 'FEB', 3 => 'MAR', 4 => 'APR',
        5 => 'MAY', 6 => 'JUN', 7 => 'JUL', 8 => 'AUG',
        9 => 'SEPT', 10 => 'OCT', 11 => 'NOV', 12 => 'DEC',
    ];

    protected $monthData;
    protected $year;
    protected $buildingName;

    public function __construct($monthData, $year, $buildingName)
    {
        $this->monthData    = $monthData;
        $this->year         = $year;
        $this->buildingName = $buildingName;
    }

    public function title(): string
    {
        return 'Consolidate';
    }

    public function array(): array
    {
        $rows = [];

        // Row 1: building name (left) | report title (right)
        // Col A = building name, Col K = report title
        $rows[] = [
            strtoupper($this->buildingName),
            '', '', '', '', '', '', '', '', '',
            'PROPERTY MANAGEMENT MONTHLY REPORT',
            '', '', '', '',
        ];

        // Row 2: empty
        $rows[] = array_fill(0, 15, '');

        // Row 3: month header row
        $headerRow = ['', 'MONTH'];
        foreach (self::$monthCols as $lbl) {
            $headerRow[] = $lbl;
        }
        $headerRow[] = 'YTD TOTAL';
        $rows[] = $headerRow;

        // Row 4: General Income Overview — value in col C (merged C4:O4 in AfterSheet)
        $rows[] = ['', '', 'GENERAL INCOME OVERVIEW', '', '', '', '', '', '', '', '', '', '', '', ''];

        // Row 5: Income as per rental agreement
        $incomes = [];
        foreach (range(1, 12) as $m) {
            $total = 0;
            foreach ($this->monthData[$m]['units'] ?? [] as $u) {
                $total += (float)($u->income_amount ?? 0);
            }
            $incomes[$m] = $total;
        }
        $incomeRow = ['', 'Income as per rental agreement'];
        foreach ($incomes as $v) {
            $incomeRow[] = $v ?: '-';
        }
        $incomeRow[] = array_sum($incomes);
        $rows[] = $incomeRow;

        // Row 6: Collection (active + old outstanding)
        $collections = [];
        foreach (range(1, 12) as $m) {
            $total = 0;
            foreach ($this->monthData[$m]['units'] ?? [] as $u) {
                $total += (float)($u->collection_amount ?? 0);
            }
            foreach ($this->monthData[$m]['old_outstanding'] ?? [] as $u) {
                $total += (float)($u->collection_amount ?? 0);
            }
            $collections[$m] = $total;
        }
        $collectionRow = ['', 'Collection'];
        foreach ($collections as $v) {
            $collectionRow[] = $v ?: '-';
        }
        $collectionRow[] = array_sum($collections);
        $rows[] = $collectionRow;

        // Row 7: empty
        $rows[] = array_fill(0, 15, '');

        // Row 8: Expenses header (orange bg) — label in col B, YTD TOTAL in col O
        $expHeaderRow = ['', 'Expenses', '', '', '', '', '', '', '', '', '', '', '', '', 'YTD TOTAL'];
        $rows[] = $expHeaderRow;

        // Row 9: EXPENSES OVERVIEW — value in col C (merged C9:O9 in AfterSheet)
        $rows[] = ['', '', 'EXPENSES OVERVIEW', '', '', '', '', '', '', '', '', '', '', '', ''];

        // Expense rows (name in col B, values in C–N, YTD in O)
        $allExpenseNames = [];
        foreach (range(1, 12) as $m) {
            foreach ($this->monthData[$m]['expenses'] ?? [] as $exp) {
                if (!in_array($exp->expense_name, $allExpenseNames)) {
                    $allExpenseNames[] = $exp->expense_name;
                }
            }
        }
        sort($allExpenseNames);

        $expenseTotalsByMonth = array_fill(1, 12, 0);
        foreach ($allExpenseNames as $expName) {
            $expenseRow = ['', $expName];
            $yearTotal  = 0;
            foreach (range(1, 12) as $m) {
                $amount = 0;
                foreach ($this->monthData[$m]['expenses'] ?? [] as $exp) {
                    if ($exp->expense_name === $expName) {
                        $amount = (float)$exp->expense_amount;
                        break;
                    }
                }
                $expenseRow[] = $amount > 0 ? $amount : '-';
                $yearTotal    += $amount;
                $expenseTotalsByMonth[$m] += $amount;
            }
            $expenseRow[] = $yearTotal ?: '-';
            $rows[] = $expenseRow;
        }

        // Total Expenses
        $totalExpRow = ['', 'Total Expenses'];
        foreach ($expenseTotalsByMonth as $v) {
            $totalExpRow[] = $v ?: '-';
        }
        $totalExpRow[] = array_sum($expenseTotalsByMonth) ?: '-';
        $rows[] = $totalExpRow;

        // Empty
        $rows[] = array_fill(0, 15, '');

        // Amount Transfer to Land Lord
        $transferRow = ['', 'Amount Transfer to Land Lord'];
        $transferYTD = 0;
        foreach (range(1, 12) as $m) {
            $transfer = $collections[$m] - $expenseTotalsByMonth[$m];
            $transferRow[] = $transfer ?: '-';
            $transferYTD  += $transfer;
        }
        $transferRow[] = $transferYTD;
        $rows[] = $transferRow;

        // Empty
        $rows[] = array_fill(0, 15, '');

        // Overdues To Date (active + old outstanding — legal tenants now included in units)
        $overdues = [];
        foreach (range(1, 12) as $m) {
            $total = 0;
            foreach ($this->monthData[$m]['units'] ?? [] as $u) {
                $total += (float)($u->outstanding_amount ?? 0);
            }
            foreach ($this->monthData[$m]['old_outstanding'] ?? [] as $u) {
                $total += (float)($u->outstanding_amount ?? 0);
            }
            $overdues[$m] = $total;
        }
        $overdueRow = ['', 'Overdues To Date'];
        foreach ($overdues as $v) {
            $overdueRow[] = $v ?: '-';
        }
        $overdueRow[] = 'NA';
        $rows[] = $overdueRow;

        // Empty
        $rows[] = array_fill(0, 15, '');

        // Operational Information — in col A (merged A:O in AfterSheet)
        $rows[] = ['Operational Information', '', '', '', '', '', '', '', '', '', '', '', '', '', ''];

        // Empty
        $rows[] = array_fill(0, 15, '');

        // Details header (same styling as month header row)
        $detailHeader = ['', 'Details'];
        foreach (self::$monthCols as $lbl) {
            $detailHeader[] = $lbl;
        }
        $detailHeader[] = 'YR TOTAL';
        $rows[] = $detailHeader;

        // Occupancy section label
        $rows[] = ['', 'Occupancy', '', '', '', '', '', '', '', '', '', '', '', '', ''];

        // Total Number of Units
        $totalUnits = $this->monthData[1]['occupancy']['total_units'] ?? 0;
        $unitRow    = ['', 'Total Number of Units', $totalUnits];
        for ($i = 2; $i <= 12; $i++) {
            $unitRow[] = '';
        }
        $unitRow[] = '';
        $rows[] = $unitRow;

        $this->addOccupancyRow($rows, 'New Leased Residential Units', 'new_leased_residential');
        $this->addOccupancyRow($rows, 'New Leased Commercial Units',  'new_leased_commercial');

        $rows[] = array_fill(0, 15, '');

        $this->addOccupancyRow($rows, 'Total Occupied Residential Units', 'occupied_residential');
        $this->addOccupancyRow($rows, 'Total Occupied Commercial Units',  'occupied_commercial');

        // Total Occupied Units (bold in AfterSheet)
        $totalOccRow = ['', 'Total Occupied Units'];
        foreach (range(1, 12) as $m) {
            $occ = $this->monthData[$m]['occupancy'] ?? [];
            $totalOccRow[] = ($occ['occupied_residential'] ?? 0) + ($occ['occupied_commercial'] ?? 0);
        }
        $totalOccRow[] = '';
        $rows[] = $totalOccRow;

        $this->addOccupancyRow($rows, 'Vacant Residential Units', 'vacant_residential');
        $this->addOccupancyRow($rows, 'Vacant Commercial Units',  'vacant_commercial');

        $rows[] = array_fill(0, 15, '');

        $this->addOccupancyRow($rows, 'Residential Units Under Evacuation', 'evacuation_residential');
        $this->addOccupancyRow($rows, 'Commercial Units Under Evacuation',  'evacuation_commercial');

        // Total Building Occupancy Level
        $occLevelRow = ['', 'Total Building Occupancy Level'];
        foreach (range(1, 12) as $m) {
            $occ      = $this->monthData[$m]['occupancy'] ?? [];
            $total    = $occ['total_units'] ?? 0;
            $occupied = ($occ['occupied_residential'] ?? 0) + ($occ['occupied_commercial'] ?? 0);
            $occLevelRow[] = $total > 0 ? round(($occupied / $total) * 100, 1) . '%' : '0%';
        }
        $occLevelRow[] = 'NA';
        $rows[] = $occLevelRow;

        // Legal section — in col A (merged, orange bg)
        $rows[] = ['Legal', '', '', '', '', '', '', '', '', '', '', '', '', '', ''];
        $rows[] = array_fill(0, 15, '');
        $rows[] = ['', 'Narration', '', '', '', '', '', '', '', '', '', '', '', '', ''];
        for ($i = 0; $i < 3; $i++) {
            $rows[] = array_fill(0, 15, '');
        }

        // Defaulters section — in col A (merged, orange bg)
        $rows[] = ['Defaulters', '', '', '', '', '', '', '', '', '', '', '', '', '', ''];
        $rows[] = array_fill(0, 15, '');
        $rows[] = ['', 'Narration', '', '', '', '', '', '', '', '', '', '', '', '', ''];
        for ($i = 0; $i < 3; $i++) {
            $rows[] = array_fill(0, 15, '');
        }

        // Notes section — in col A (merged, blue bg)
        $rows[] = ['Notes', '', '', '', '', '', '', '', '', '', '', '', '', '', ''];
        foreach (['Maintenance', 'Collections', 'Defaulters', 'Legal', 'Overall Buildings Conditions', 'Required Approvals', 'Recommendations', 'Other'] as $item) {
            $rows[] = ['', $item, '', '', '', '', '', '', '', '', '', '', '', '', ''];
        }

        return $rows;
    }

    private function addOccupancyRow(&$rows, $label, $key)
    {
        $row = ['', $label];
        foreach (range(1, 12) as $m) {
            $val = $this->monthData[$m]['occupancy'][$key] ?? 0;
            $row[] = $val ?: '-';
        }
        $row[] = 'NA';
        $rows[] = $row;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet   = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();
                $thin    = \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN;
                $medium  = \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM;

                // ── 1. Default font: Century Gothic 9 ────────────────────────
                $sheet->getStyle('A1:O' . $lastRow)->applyFromArray([
                    'font' => ['name' => 'Century Gothic', 'size' => 9],
                ]);

                // ── 2. Row 1: building name (left) + report title (right) ────
                // Merge A1:I1 for building name
                $sheet->mergeCells('A1:I1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font'      => ['name' => 'Century Gothic', 'bold' => true, 'size' => 10],
                    'alignment' => ['horizontal' => 'left', 'vertical' => 'center'],
                ]);
                // Merge J1:O1 for report title
                $sheet->mergeCells('J1:O1');
                $sheet->getStyle('J1')->applyFromArray([
                    'font'      => ['name' => 'Century Gothic', 'bold' => true, 'size' => 10],
                    'alignment' => ['horizontal' => 'right', 'vertical' => 'center'],
                ]);

                // ── 3. Row 3: Month header ───────────────────────────────────
                $sheet->getStyle('A3:O3')->applyFromArray([
                    'font'      => ['name' => 'Century Gothic', 'bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                    'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => '2E75B6']],
                    'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                    'borders'   => ['outline' => ['borderStyle' => $medium, 'color' => ['rgb' => '1F4E79']]],
                ]);

                // ── 4. Row 4: General Income Overview (merge C4:O4) ──────────
                $sheet->mergeCells('C4:O4');
                $sheet->getStyle('A4:O4')->applyFromArray([
                    'font'      => ['name' => 'Century Gothic', 'bold' => true, 'size' => 10],
                    'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => 'DEEAF1']],
                    'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                    'borders'   => ['outline' => ['borderStyle' => $medium, 'color' => ['rgb' => '2E75B6']]],
                ]);

                // ── 5. Rows 5–6: Income & Collection data ────────────────────
                $sheet->getStyle('A5:O6')->applyFromArray([
                    'borders' => [
                        'allBorders' => ['borderStyle' => $thin, 'color' => ['rgb' => 'BDD7EE']],
                        'outline'    => ['borderStyle' => $medium, 'color' => ['rgb' => '2E75B6']],
                    ],
                ]);
                $sheet->getStyle('B5:B6')->applyFromArray([
                    'font' => ['name' => 'Century Gothic', 'bold' => true, 'size' => 9],
                ]);
                $sheet->getStyle('C5:O6')->applyFromArray([
                    'alignment' => ['horizontal' => 'center'],
                ]);

                // ── 6. Row 8: Expenses header (orange bg) ───────────────────
                $sheet->getStyle('A8:O8')->applyFromArray([
                    'font'      => ['name' => 'Century Gothic', 'bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                    'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => 'ED7D31']],
                    'alignment' => ['vertical' => 'center'],
                    'borders'   => ['outline' => ['borderStyle' => $medium, 'color' => ['rgb' => 'C55A11']]],
                ]);

                // ── 7. Row 9: Expenses Overview (merge C9:O9) ───────────────
                $sheet->mergeCells('C9:O9');
                $sheet->getStyle('A9:O9')->applyFromArray([
                    'font'      => ['name' => 'Century Gothic', 'bold' => true, 'size' => 10],
                    'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => 'DEEAF1']],
                    'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                    'borders'   => ['outline' => ['borderStyle' => $medium, 'color' => ['rgb' => '2E75B6']]],
                ]);

                // ── 8. Scan remaining rows ───────────────────────────────────
                for ($r = 10; $r <= $lastRow; $r++) {
                    $cellA = trim((string)$sheet->getCell('A' . $r)->getValue());
                    $cellB = trim((string)$sheet->getCell('B' . $r)->getValue());

                    if (in_array($cellA, ['Operational Information', 'Notes'], true)) {
                        // Full-width blue merge (same as month header)
                        $sheet->mergeCells('A' . $r . ':O' . $r);
                        $sheet->getStyle('A' . $r . ':O' . $r)->applyFromArray([
                            'font'      => ['name' => 'Century Gothic', 'bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                            'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => '2E75B6']],
                            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                            'borders'   => ['outline' => ['borderStyle' => $medium, 'color' => ['rgb' => '1F4E79']]],
                        ]);
                    } elseif (in_array($cellA, ['Legal', 'Defaulters'], true)) {
                        // Full-width orange merge
                        $sheet->mergeCells('A' . $r . ':O' . $r);
                        $sheet->getStyle('A' . $r . ':O' . $r)->applyFromArray([
                            'font'      => ['name' => 'Century Gothic', 'bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                            'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => 'ED7D31']],
                            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                            'borders'   => ['outline' => ['borderStyle' => $medium, 'color' => ['rgb' => 'C55A11']]],
                        ]);
                    } elseif ($cellB === 'Details') {
                        // Details header — same as month header (blue)
                        $sheet->getStyle('A' . $r . ':O' . $r)->applyFromArray([
                            'font'      => ['name' => 'Century Gothic', 'bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                            'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => '2E75B6']],
                            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                            'borders'   => ['outline' => ['borderStyle' => $medium, 'color' => ['rgb' => '1F4E79']]],
                        ]);
                    } elseif ($cellB === 'Occupancy') {
                        // Occupancy section label — light grey
                        $sheet->getStyle('A' . $r . ':O' . $r)->applyFromArray([
                            'font'    => ['name' => 'Century Gothic', 'bold' => true, 'size' => 10],
                            'fill'    => ['fillType' => 'solid', 'startColor' => ['rgb' => 'F2F2F2']],
                            'borders' => ['outline' => ['borderStyle' => $medium, 'color' => ['rgb' => '2E75B6']]],
                        ]);
                    } elseif (in_array($cellB, ['Total Expenses', 'Amount Transfer to Land Lord', 'Total Occupied Units', 'Total Building Occupancy Level'], true)) {
                        // Summary/total rows — light blue bg, bold
                        $sheet->getStyle('A' . $r . ':O' . $r)->applyFromArray([
                            'font'      => ['name' => 'Century Gothic', 'bold' => true, 'size' => 10],
                            'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => 'BDD7EE']],
                            'alignment' => ['horizontal' => 'center'],
                            'borders'   => ['outline' => ['borderStyle' => $medium, 'color' => ['rgb' => '2E75B6']]],
                        ]);
                        $sheet->getStyle('B' . $r)->applyFromArray([
                            'alignment' => ['horizontal' => 'left'],
                        ]);
                    } elseif ($cellB === 'Overdues To Date') {
                        // Overdues — bold, light orange bg
                        $sheet->getStyle('A' . $r . ':O' . $r)->applyFromArray([
                            'font'      => ['name' => 'Century Gothic', 'bold' => true, 'size' => 10],
                            'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => 'FCE4D6']],
                            'alignment' => ['horizontal' => 'center'],
                            'borders'   => ['outline' => ['borderStyle' => $medium, 'color' => ['rgb' => 'C55A11']]],
                        ]);
                        $sheet->getStyle('B' . $r)->applyFromArray([
                            'alignment' => ['horizontal' => 'left'],
                        ]);
                    } elseif ($cellB !== '') {
                        // Regular row — borders + centered data columns
                        $sheet->getStyle('A' . $r . ':O' . $r)->applyFromArray([
                            'borders' => ['allBorders' => ['borderStyle' => $thin, 'color' => ['rgb' => 'BDD7EE']]],
                        ]);
                        $sheet->getStyle('C' . $r . ':O' . $r)->applyFromArray([
                            'alignment' => ['horizontal' => 'center'],
                        ]);
                    }
                }

                // ── 9. YTD TOTAL column (O) — light blue tint on data rows ──
                $sheet->getStyle('O5:O6')->applyFromArray([
                    'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => 'DEEAF1']],
                    'font'      => ['name' => 'Century Gothic', 'bold' => true, 'size' => 9],
                    'alignment' => ['horizontal' => 'center'],
                ]);
            },
        ];
    }
}
