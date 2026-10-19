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
 * One month of the Repair & Maintenance Report, laid out like the back office's own building workbook:
 * an In-house table (one row per service report dated in the month: complaint, technician, material / labour /
 * total from the service report) and a Sub-contract table (vendor invoice lines dated in the month), each with a
 * SUM total row.
 */
class RepairMaintenanceMonthSheet implements FromArray, WithEvents, WithTitle, WithStrictNullComparison
{
    const IN_HOUSE_HEAD = ['Complaint Number', 'Service Report No', 'Complaint type', 'Date', 'Building Name', 'Location/Unit No',
        'Description', 'Technician', 'Category', 'Material Charges', 'Labour Charges', 'Total Amount RO'];
    const SUB_HEAD = ['Sr No', 'Complaint type', 'Date', 'Building Name', 'Location/Unit No', 'Description',
        'Sub Contract', 'Category', 'Total Amount RO'];

    protected $buildingName;
    protected $month;
    protected $year;
    protected $inHouse;
    protected $subContract;
    protected $startDay;

    // row numbers filled by array(), used by AfterSheet for styling
    private $inHead;
    private $inFirst;
    private $inTotal;
    private $subHead;
    private $subFirst;
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
        $rows   = [];
        $period = RepairMaintenanceReport::periodLabel($this->year, $this->month, $this->startDay);
        $rows[] = [$this->buildingName . ' - Repair & Maintenance - ' . date('F Y', mktime(0, 0, 0, $this->month, 1, $this->year))
            . ($period ? ' (' . $period . ')' : '')];
        $rows[] = [''];
        $rows[] = ['In-house'];
        $rows[] = self::IN_HOUSE_HEAD;
        $this->inHead  = count($rows);
        $this->inFirst = $this->inHead + 1;
        foreach ($this->inHouse as $r) {
            $rows[] = [
                $r->complaint_no ?: '',
                $r->service_report_no,
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
            $rows[] = ['No service reports this month'];
        }
        $last   = count($rows);
        $rows[] = count($this->inHouse) > 0
            ? ['', '', '', '', '', '', '', '', 'Total', "=SUM(J{$this->inFirst}:J{$last})", "=SUM(K{$this->inFirst}:K{$last})", "=SUM(L{$this->inFirst}:L{$last})"]
            : ['', '', '', '', '', '', '', '', 'Total', 0, 0, 0];
        $this->inTotal = count($rows);

        $rows[] = [''];
        $rows[] = [''];
        $rows[] = ['Sub-contract'];
        $rows[] = self::SUB_HEAD;
        $this->subHead  = count($rows);
        $this->subFirst = $this->subHead + 1;
        $n = 0;
        foreach ($this->subContract as $r) {
            $rows[] = [
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
            $rows[] = ['No vendor invoices this month'];
        }
        $last   = count($rows);
        $rows[] = $n > 0
            ? ['', '', '', '', '', '', '', 'Total', "=SUM(I{$this->subFirst}:I{$last})"]
            : ['', '', '', '', '', '', '', 'Total', 0];
        $this->subTotal = count($rows);

        return $rows;
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
                $thin = ['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]];
                $head = $thin + [
                    'font'      => ['bold' => true],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFF00']],
                    'alignment' => ['horizontal' => 'center', 'vertical' => 'center', 'wrapText' => true],
                ];

                $s->getStyle('A1')->getFont()->setBold(true)->setSize(13);
                $s->getStyle('A3')->getFont()->setBold(true);
                $s->getStyle('A' . ($this->subHead - 1))->getFont()->setBold(true);

                $s->getStyle("A{$this->inHead}:L{$this->inHead}")->applyFromArray($head);
                $s->getStyle("A{$this->inFirst}:L{$this->inTotal}")->applyFromArray($thin);
                $s->getStyle("J{$this->inFirst}:L{$this->inTotal}")->getNumberFormat()->setFormatCode('0.000');
                $s->getStyle("D{$this->inFirst}:D{$this->inTotal}")->getNumberFormat()->setFormatCode('dd.mm.yyyy');
                $s->getStyle("I{$this->inTotal}:L{$this->inTotal}")->getFont()->setBold(true);

                $s->getStyle("A{$this->subHead}:I{$this->subHead}")->applyFromArray($head);
                $s->getStyle("A{$this->subFirst}:I{$this->subTotal}")->applyFromArray($thin);
                $s->getStyle("I{$this->subFirst}:I{$this->subTotal}")->getNumberFormat()->setFormatCode('0.000');
                $s->getStyle("C{$this->subFirst}:C{$this->subTotal}")->getNumberFormat()->setFormatCode('dd.mm.yyyy');
                $s->getStyle("H{$this->subTotal}:I{$this->subTotal}")->getFont()->setBold(true);

                $s->getStyle("A{$this->inFirst}:L{$this->subTotal}")->getAlignment()->setVertical('top');
                $s->getStyle("G{$this->inFirst}:G{$this->inTotal}")->getAlignment()->setWrapText(true);
                $s->getStyle("F{$this->subFirst}:F{$this->subTotal}")->getAlignment()->setWrapText(true);
                $s->getStyle("A{$this->inFirst}:A{$this->subTotal}")->getAlignment()->setHorizontal('left');

                $widths = ['A' => 16, 'B' => 16, 'C' => 14, 'D' => 14, 'E' => 22, 'F' => 40, 'G' => 40, 'H' => 22, 'I' => 18, 'J' => 15, 'K' => 15, 'L' => 16];
                foreach ($widths as $col => $w) {
                    $s->getColumnDimension($col)->setWidth($w);
                }
                $s->getRowDimension($this->inHead)->setRowHeight(30);
                $s->getRowDimension($this->subHead)->setRowHeight(30);
                $s->freezePane('A' . $this->inFirst);
            },
        ];
    }
}
