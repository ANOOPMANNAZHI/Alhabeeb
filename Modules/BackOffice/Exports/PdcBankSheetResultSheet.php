<?php

namespace Modules\BackOffice\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;

/**
 * One sheet of the bank-sheet result workbook, from the arrays built by
 * BankSheetResultRows: bold headings and total, amounts as 3-decimal numbers.
 * Strings stay text (cheque / receipt numbers keep leading zeros); only real
 * PHP numbers become numeric cells.
 */
class PdcBankSheetResultSheet extends DefaultValueBinder implements FromArray, WithTitle, ShouldAutoSize, WithColumnFormatting, WithEvents, WithCustomValueBinder
{
    protected $title;
    protected $sheet;

    /** @param array $sheet ['rows', 'heading_row', 'total_row', 'amount_column'] */
    public function __construct($title, array $sheet)
    {
        $this->title = $title;
        $this->sheet = $sheet;
    }

    public function array(): array
    {
        return $this->sheet['rows'];
    }

    public function title(): string
    {
        return $this->title;
    }

    public function bindValue(Cell $cell, mixed $value): bool
    {
        if (is_string($value)) {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);
            return true;
        }
        return parent::bindValue($cell, $value);
    }

    public function columnFormats(): array
    {
        return [$this->sheet['amount_column'] => '#,##0.000'];
    }

    public function registerEvents(): array
    {
        $sheet = $this->sheet;
        return [
            AfterSheet::class => function (AfterSheet $event) use ($sheet) {
                $ws = $event->sheet->getDelegate();
                $lastCol = $ws->getHighestColumn();

                $h = $sheet['heading_row'];
                $ws->getStyle('A' . $h . ':' . $lastCol . $h)->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1F4E79']],
                ]);
                $ws->freezePane('A' . ($h + 1));

                // summary labels above the headings (receipts sheet only)
                if ($h > 1) {
                    $ws->getStyle('A1:A' . ($h - 2))->getFont()->setBold(true);
                }
                if ($sheet['total_row']) {
                    $ws->getStyle('A' . $sheet['total_row'] . ':' . $lastCol . $sheet['total_row'])->getFont()->setBold(true);
                }
            },
        ];
    }
}
