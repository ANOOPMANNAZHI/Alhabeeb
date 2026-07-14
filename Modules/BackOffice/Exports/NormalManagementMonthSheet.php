<?php

namespace Modules\BackOffice\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class NormalManagementMonthSheet implements FromArray, ShouldAutoSize, WithEvents, WithTitle
{
    private static $monthNames = [
        1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
        5 => 'May', 6 => 'June', 7 => 'July', 8 => 'Aug',
        9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec',
    ];

    private static $monthLabels = [
        1 => 'JAN', 2 => 'FEB', 3 => 'MAR', 4 => 'APR',
        5 => 'MAY', 6 => 'JUN', 7 => 'JUL', 8 => 'AUG',
        9 => 'SEP', 10 => 'OCT', 11 => 'NOV', 12 => 'DEC',
    ];

    protected $data;
    protected $month;
    protected $year;
    protected $buildingName;
    private $legalRowIndices = [];

    public function __construct($data, $month, $year, $buildingName)
    {
        $this->data         = $data;
        $this->month        = $month;
        $this->year         = $year;
        $this->buildingName = $buildingName;
    }

    public function title(): string
    {
        return self::$monthNames[$this->month];
    }

    public function array(): array
    {
        $units    = $this->data['units'] ?? [];
        $monthLbl = self::$monthLabels[$this->month] . "'" . substr((string)$this->year, 2);
        $lastDay  = date('d/m/Y', mktime(0, 0, 0, $this->month + 1, 0, $this->year));

        $rows = [];

        // Row 1: building name (merged A1:J1 in AfterSheet)
        $rows[] = [strtoupper($this->buildingName), '', '', '', '', '', '', '', '', ''];

        // Row 2: empty
        $rows[] = ['', '', '', '', '', '', '', '', '', ''];

        // Row 3: column headers (10 columns)
        $rows[] = [
            'UNIT NO', 'TYPE', 'TENANT NAME',
            'CONTRACT', 'CONTRACT',
            'RENT RECD', 'RENT PER', 'COLLECTION IN', 'INCOME', 'OUTSTANDING',
        ];

        // Row 4: sub-headers
        $rows[] = [
            '', '', '',
            'START', 'END',
            'UPTO', 'MONTH', $monthLbl, 'FOR ' . $monthLbl, 'AS ON ' . $lastDay,
        ];

        // Data rows
        $totalRentPer     = 0;
        $totalCollection  = 0;
        $totalIncome      = 0;
        $totalOutstanding = 0;

        foreach ($units as $unit) {
            $rentRecd      = !empty($unit->rent_recd_upto)
                ? date('d.m.Y', strtotime($unit->rent_recd_upto))
                : '-';
            $contractStart = !empty($unit->contract_start)
                ? date('d.m.Y', strtotime($unit->contract_start))
                : '-';
            $contractEnd   = !empty($unit->contract_end)
                ? date('d.m.Y', strtotime($unit->contract_end))
                : '-';
            $rentPer       = (float)($unit->rent_per_month ?? 0);
            $collection    = (float)($unit->collection_amount ?? 0);
            $income        = (float)($unit->income_amount ?? 0);
            $outstanding   = (float)($unit->outstanding_amount ?? 0);

            $totalRentPer     += $rentPer;
            $totalCollection  += $collection;
            $totalIncome      += $income;
            $totalOutstanding += $outstanding;

            if (!empty($unit->is_legal)) {
                $this->legalRowIndices[] = count($rows) + 1; // 1-based Excel row
            }

            $rows[] = [
                $unit->unit_no,
                $unit->unit_type ?? '',
                $unit->tenant_name ?? '',
                $contractStart,
                $contractEnd,
                $rentRecd,
                $rentPer,
                $collection,
                $income,
                $outstanding,
            ];
        }

        // Total row
        $rows[] = ['', '', 'TOTAL COLLECTION', '', '', '', $totalRentPer, $totalCollection, $totalIncome, $totalOutstanding];

        // Old Outstanding section
        $oldOutstanding = $this->data['old_outstanding'] ?? [];
        if (!empty($oldOutstanding)) {
            $rows[] = ['', '', '', '', '', '', '', '', '', ''];
            $rows[] = ['Old Outstanding', '', '', '', '', '', '', '', '', ''];

            $oldTotalRentPer     = 0;
            $oldTotalCollection  = 0;
            $oldTotalOutstanding = 0;

            foreach ($oldOutstanding as $unit) {
                $rentRecd      = !empty($unit->rent_recd_upto)
                    ? date('d.m.Y', strtotime($unit->rent_recd_upto))
                    : '-';
                $contractStart = !empty($unit->contract_start)
                    ? date('d.m.Y', strtotime($unit->contract_start))
                    : '-';
                $contractEnd   = !empty($unit->contract_end)
                    ? date('d.m.Y', strtotime($unit->contract_end))
                    : '-';
                $rentPer     = (float)($unit->rent_per_month ?? 0);
                $collection  = (float)($unit->collection_amount ?? 0);
                $outstanding = (float)($unit->outstanding_amount ?? 0);

                $oldTotalRentPer    += $rentPer;
                $oldTotalCollection += $collection;
                $oldTotalOutstanding += $outstanding;

                $rows[] = [
                    $unit->unit_no,
                    $unit->unit_type ?? '',
                    $unit->tenant_name ?? '',
                    $contractStart,
                    $contractEnd,
                    $rentRecd,
                    $rentPer,
                    $collection,
                    '',
                    $outstanding,
                ];
            }

            $rows[] = ['', '', 'TOTAL OLD OUTSTANDING', '', '', '', $oldTotalRentPer, $oldTotalCollection, '', $oldTotalOutstanding];
            $rows[] = ['', '', 'GRAND TOTAL', '', '', '', '', $totalCollection + $oldTotalCollection, '', $totalOutstanding + $oldTotalOutstanding];
        }

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet   = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();

                $thin   = \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN;
                $medium = \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM;

                // Apply Century Gothic size 9 to entire sheet as default
                $sheet->getStyle('A1:J' . $lastRow)->applyFromArray([
                    'font' => ['name' => 'Century Gothic', 'size' => 9],
                ]);

                // Borders on data area (rows 3 onwards)
                $sheet->getStyle('A3:J' . $lastRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => ['borderStyle' => $thin,   'color' => ['rgb' => 'BDD7EE']],
                        'outline'    => ['borderStyle' => $medium,  'color' => ['rgb' => '1F4E79']],
                    ],
                ]);

                // Row 1: building name — merge A1:J1, center, bold size 10, dark navy bg
                $sheet->mergeCells('A1:J1');
                $sheet->getStyle('A1:J1')->applyFromArray([
                    'font'      => ['name' => 'Century Gothic', 'bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                    'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => '1F4E79']],
                    'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                ]);

                // Row 3: column headers — blue bg, white text, bold size 10, centered
                $sheet->getStyle('A3:J3')->applyFromArray([
                    'font'      => ['name' => 'Century Gothic', 'bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                    'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => '045DC2']],
                    'alignment' => ['horizontal' => 'center'],
                    'borders'   => ['outline' => ['borderStyle' => $medium, 'color' => ['rgb' => '1F4E79']]],
                ]);

                // Row 4: sub-headers — bold size 10, centered
                $sheet->getStyle('A4:J4')->applyFromArray([
                    'font'      => ['name' => 'Century Gothic', 'bold' => true, 'size' => 10],
                    'alignment' => ['horizontal' => 'center'],
                    'borders'   => ['outline' => ['borderStyle' => $medium, 'color' => ['rgb' => '1F4E79']]],
                ]);

                // Data rows (5 to last): columns A-E centered, amounts (G-J) right-aligned
                if ($lastRow >= 5) {
                    $sheet->getStyle('A5:E' . $lastRow)->applyFromArray([
                        'alignment' => ['horizontal' => 'center'],
                    ]);
                    $sheet->getStyle('G5:J' . $lastRow)->applyFromArray([
                        'alignment' => ['horizontal' => 'right'],
                    ]);
                }

                // Scan rows: highlight TOTAL and section header rows
                for ($r = 5; $r <= $lastRow; $r++) {
                    $cellA = $sheet->getCell('A' . $r)->getValue();
                    $cellC = $sheet->getCell('C' . $r)->getValue();

                    if ($cellA === 'Old Outstanding') {
                        // Old Outstanding header — dark navy bg, white text, bold size 10
                        $sheet->getStyle('A' . $r . ':J' . $r)->applyFromArray([
                            'font'    => ['name' => 'Century Gothic', 'bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                            'fill'    => ['fillType' => 'solid', 'startColor' => ['rgb' => '1F4E79']],
                            'borders' => ['outline' => ['borderStyle' => $medium, 'color' => ['rgb' => '1F4E79']]],
                        ]);
                    } elseif ($cellC === 'TOTAL COLLECTION') {
                        // Total Collection — light blue bg, bold size 10
                        $sheet->getStyle('A' . $r . ':J' . $r)->applyFromArray([
                            'font'    => ['name' => 'Century Gothic', 'bold' => true, 'size' => 10],
                            'fill'    => ['fillType' => 'solid', 'startColor' => ['rgb' => 'BDD7EE']],
                            'borders' => ['outline' => ['borderStyle' => $medium, 'color' => ['rgb' => '1F4E79']]],
                        ]);
                    } elseif ($cellC === 'TOTAL OLD OUTSTANDING') {
                        // Total Old Outstanding — light orange bg, bold size 10
                        $sheet->getStyle('A' . $r . ':J' . $r)->applyFromArray([
                            'font'    => ['name' => 'Century Gothic', 'bold' => true, 'size' => 10],
                            'fill'    => ['fillType' => 'solid', 'startColor' => ['rgb' => 'FCE4D6']],
                            'borders' => ['outline' => ['borderStyle' => $medium, 'color' => ['rgb' => 'C55A11']]],
                        ]);
                    } elseif ($cellC === 'GRAND TOTAL') {
                        // Grand Total — dark orange bg, white text, bold size 10
                        $sheet->getStyle('A' . $r . ':J' . $r)->applyFromArray([
                            'font'    => ['name' => 'Century Gothic', 'bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                            'fill'    => ['fillType' => 'solid', 'startColor' => ['rgb' => 'C55A11']],
                            'borders' => ['outline' => ['borderStyle' => $medium, 'color' => ['rgb' => 'C55A11']]],
                        ]);
                    } elseif (in_array($r, $this->legalRowIndices)) {
                        // Legal data rows — red background
                        $sheet->getStyle('A' . $r . ':J' . $r)->applyFromArray([
                            'font'    => ['name' => 'Century Gothic', 'size' => 9, 'color' => ['rgb' => '7B0000']],
                            'fill'    => ['fillType' => 'solid', 'startColor' => ['rgb' => 'FFE7E7']],
                        ]);
                    }
                }
            },
        ];
    }
}
