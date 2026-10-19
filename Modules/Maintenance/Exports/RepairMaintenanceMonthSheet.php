<?php

namespace Modules\Maintenance\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use Modules\Maintenance\Services\RepairMaintenanceReport;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/**
 * One month of the Repair & Maintenance Report in the back office's own building workbook layout
 * ("Al Themar Bldg-Repair & Maintenance 2025-2026.xlsx", Sep 2026 sheet): tables from B3, bold Calibri 11,
 * yellow headers, thin borders, light orange SUM cells, its column widths and row heights.
 * In-house (B:L): one row per service report dated in the month; complaint number without the CMP prefix.
 * Sub-contract (B:J), one gap row below: vendor invoice lines dated in the month.
 */
class RepairMaintenanceMonthSheet implements FromArray, WithEvents, WithTitle, WithStrictNullComparison
{
    const IN_HOUSE_HEAD = ['Complaint Number', 'Complaint type', 'Date', 'Building Name', 'Location/Unit No', 'Description',
        'Technician', 'Category', 'Material Charges', 'Labour Charges', 'Total Amount RO'];
    const SUB_HEAD = ['Sr No', 'Complaint type', 'Date', 'Building Name', 'Location/Unit No', 'Description',
        'Sub Contract', 'Category', 'Total Amount RO'];

    const YELLOW = 'FFFF00';
    const TOTAL_FILL = 'FBE5D6';      // Orange, Accent 2, lighter 80% (theme 5, tint 0.8 in the reference)
    const WIDTHS = ['A' => 13, 'B' => 13.14, 'C' => 13.86, 'D' => 16.86, 'E' => 20.43, 'F' => 14.57, 'G' => 45.14,
        'H' => 18.86, 'I' => 14.29, 'J' => 12, 'K' => 13, 'L' => 17];
    const HEAD_HEIGHT = 45.75;
    const SUB_HEAD_HEIGHT = 41.25;
    const ROW_HEIGHT = 24.75;

    protected $buildingName;
    protected $month;
    protected $year;
    protected $inHouse;
    protected $subContract;
    protected $startDay;

    // row numbers filled by array(), used by AfterSheet for styling
    private $inHead;
    private $inTotal;
    private $subHead;
    private $subTotal;

    public function __construct($buildingName, $month, $year, array $inHouse, array $subContract, $startDay = 1)
    {
        $this->startDay     = (int) $startDay;
        $this->buildingName = $buildingName;
        $this->month        = (int) $month;
        $this->year         = (int) $year;
        $this->inHouse      = $inHouse;
        $this->subContract  = $subContract;
    }

    public function title(): string
    {
        return date('M Y', mktime(0, 0, 0, $this->month, 1, $this->year));
    }

    public function array(): array
    {
        $period = RepairMaintenanceReport::periodLabel($this->year, $this->month, $this->startDay);
        $rows   = [];
        // row 1: name of the month in B1 (rows 1-2 are blank in the reference); row 2 blank
        $rows[] = ['', $this->buildingName . ' - Repair & Maintenance - ' . date('F Y', mktime(0, 0, 0, $this->month, 1, $this->year))
            . ($period ? ' (' . $period . ')' : '')];
        $rows[] = [''];

        $rows[] = array_merge([''], self::IN_HOUSE_HEAD);
        $this->inHead = count($rows);
        foreach ($this->inHouse as $r) {
            $rows[] = [
                '',
                self::complaintNumber($r->complaint_no),
                RepairMaintenanceReport::complaintType($r->priority),
                self::excelDate($r->sr_date),
                $this->buildingName,
                $r->unit_no ?: 'Common',
                $r->description ?: '',
                $r->technician ?: '',
                $r->category ?: '',
                (float) $r->material,
                (float) $r->labour,
                (float) $r->amount,
            ];
        }
        if (count($this->inHouse) === 0) {
            $rows[] = [''];
        }
        $first  = $this->inHead + 1;
        $last   = count($rows);
        $rows[] = ['', '', '', '', '', '', '', '', '', "=SUM(J{$first}:J{$last})", "=SUM(K{$first}:K{$last})", "=SUM(L{$first}:L{$last})"];
        $this->inTotal = count($rows);

        $rows[] = [''];
        $rows[] = array_merge([''], self::SUB_HEAD);
        $this->subHead = count($rows);
        $n = 0;
        foreach ($this->subContract as $r) {
            $rows[] = [
                '',
                ++$n,
                'Normal',
                self::excelDate($r->invoice_date),
                $this->buildingName,
                $r->unit_no ?: 'Common',
                $r->description ?: '',
                $r->vendor_name ?: '',
                $r->category ?: '',
                (float) $r->amount,
            ];
        }
        if ($n === 0) {
            $rows[] = [''];
        }
        $first  = $this->subHead + 1;
        $last   = count($rows);
        $rows[] = ['', '', '', '', '', '', '', '', '', "=SUM(J{$first}:J{$last})"];
        $this->subTotal = count($rows);

        return $rows;
    }

    /** "CMP44769" -> 44769 (a number, as in the reference); several complaints -> "44769, 44770". */
    private static function complaintNumber($nos)
    {
        if (!$nos) {
            return '';
        }
        $parts = array_map(function ($n) { return preg_replace('/^CMP/i', '', trim($n)); }, explode(',', $nos));
        return count($parts) === 1 && ctype_digit($parts[0]) ? (int) $parts[0] : implode(', ', $parts);
    }

    /** A calendar date as an Excel date serial (no timezone shift), or '' when empty. */
    private static function excelDate($value)
    {
        if (!$value) {
            return '';
        }
        $d = substr($value, 0, 10);
        return ExcelDate::formattedPHPToExcel((int) substr($d, 0, 4), (int) substr($d, 5, 2), (int) substr($d, 8, 2));
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $s = $event->sheet->getDelegate();
                $thin   = ['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]]];
                $yellow = ['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::YELLOW]]];
                $orange = ['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::TOTAL_FILL]]];

                // whole sheet: bold Calibri 11, as the reference
                $s->getParent()->getDefaultStyle()->getFont()->setName('Calibri')->setSize(11);
                $s->getStyle('A1:L' . $this->subTotal)->getFont()->setName('Calibri')->setSize(11)->setBold(true);

                foreach (self::WIDTHS as $col => $w) {
                    $s->getColumnDimension($col)->setWidth($w);
                }
                for ($r = $this->inHead + 1; $r <= $this->subTotal; $r++) {
                    $s->getRowDimension($r)->setRowHeight(self::ROW_HEIGHT);
                }
                $s->getRowDimension($this->inHead)->setRowHeight(self::HEAD_HEIGHT);
                $s->getRowDimension($this->subHead)->setRowHeight(self::SUB_HEAD_HEIGHT);

                // In-house: header, rows and total B:L, SUM cells J:L light orange
                $s->getStyle("B{$this->inHead}:L{$this->inHead}")->applyFromArray($thin + $yellow);
                $s->getStyle("B{$this->inHead}:L{$this->inHead}")->getAlignment()->setWrapText(true);
                $s->getStyle('B' . ($this->inHead + 1) . ":L{$this->inTotal}")->applyFromArray($thin);
                if ($this->inTotal - 1 > $this->inHead) { // data rows are white-filled in the reference
                    $s->getStyle('B' . ($this->inHead + 1) . ':L' . ($this->inTotal - 1))
                        ->applyFromArray(['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFFFF']]]);
                }
                $s->getStyle("J{$this->inTotal}:L{$this->inTotal}")->applyFromArray($orange);
                $s->getStyle('J' . ($this->inHead + 1) . ":L{$this->inTotal}")->getNumberFormat()->setFormatCode('0.000');
                $s->getStyle('D' . ($this->inHead + 1) . ":D{$this->inTotal}")->getNumberFormat()->setFormatCode('dd.mm.yyyy');
                $s->getStyle('B' . ($this->inHead + 1) . ":L{$this->inTotal}")->getAlignment()->setWrapText(true);

                // Sub-contract: header B:J, rows and total B:J, SUM cell J light orange
                $s->getStyle("B{$this->subHead}:J{$this->subHead}")->applyFromArray($thin + $yellow);
                $s->getStyle("B{$this->subHead}:J{$this->subHead}")->getAlignment()->setWrapText(true);
                $s->getStyle('B' . ($this->subHead + 1) . ":J{$this->subTotal}")->applyFromArray($thin);
                $s->getStyle("J{$this->subTotal}")->applyFromArray($orange);
                $s->getStyle('J' . ($this->subHead + 1) . ":J{$this->subTotal}")->getNumberFormat()->setFormatCode('0.000');
                $s->getStyle('D' . ($this->subHead + 1) . ":D{$this->subTotal}")->getNumberFormat()->setFormatCode('dd.mm.yyyy');
                $s->getStyle('G' . ($this->subHead + 1) . ":G{$this->subTotal}")->getAlignment()->setWrapText(true);

                // alignment as the reference: number and unit centred, description left; those headers centred
                foreach ([$this->inHead, $this->subHead] as $h) {
                    foreach (['B', 'F', 'G'] as $col) {
                        $s->getStyle($col . $h)->getAlignment()->setHorizontal('center');
                    }
                }
                $s->getStyle('B' . ($this->inHead + 1) . ":B{$this->subTotal}")->getAlignment()->setHorizontal('center');
                $s->getStyle('F' . ($this->inHead + 1) . ":F{$this->subTotal}")->getAlignment()->setHorizontal('center');
                $s->getStyle('G' . ($this->inHead + 1) . ":G{$this->inTotal}")->getAlignment()->setHorizontal('left');
                $s->getStyle('B1')->getAlignment()->setHorizontal('left')->setWrapText(false);
            },
        ];
    }
}
