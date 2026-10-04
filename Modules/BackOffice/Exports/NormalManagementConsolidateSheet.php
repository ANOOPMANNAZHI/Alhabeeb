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

    const AMC        = 'Annual Maint. Cont. - A C, FAS, Lift & Others';
    const REPAIR_A   = 'A C, FAS, Lift, etc. Repair Charges - (a)';
    const REPAIR_B   = 'Electric, Plumbing, Repair & Maint - (b)';

    /** Expense rows of the consolidated sheet, in report order (user, 03/10/2026). */
    const LABELS = [
        'Municipal Tax',
        'Management Fees',          // landlord contract, Normal buildings only
        'Value Added Tax (VAT)',    // VAT_RATE of Repair (b) + Management Fees + Cleaning Charges
        self::AMC,
        'Sewage Removal',
        'Common Area E/Water Charges',
        'Legal Expenses',           // no expense head yet
        self::REPAIR_A,
        self::REPAIR_B,
        'Cleaning Charges',         // landlord contract
    ];

    const VAT_RATE = 0.05;

    /** Expense heads that go to one row whatever the bill. */
    const BY_NAME = [
        'MUNICIPAL TAX'       => 'Municipal Tax',
        'DEWATERING'          => 'Sewage Removal',
        'ELECTRICITY & WATER' => 'Common Area E/Water Charges',
    ];

    /** AMC accounts, used from AMC_CODES_FROM: A/C, fire alarm, lift. */
    const AMC_CODES = [22307, 22308, 22309];
    /** A/C, fire alarm and lift repair accounts. */
    const REPAIR_A_CODES = [41107, 41108, 41109];
    /** Routine & Maintenance accounts (normal and CIT). */
    const REPAIR_B_CODES = [41110, 22310];

    /** Bills from this date follow the account codes alone (user, 04/10/2026). */
    const AMC_CODES_FROM = '2026-10-01';

    /**
     * Before AMC_CODES_FROM, contract (AMC) work by these vendors was booked to
     * Routine & Maintenance; it is moved to the AMC row. Pest control, lifts,
     * fire & safety, garden and pools (user, 04/10/2026).
     */
    const OLD_AMC_VENDORS = [
        619,    // MUSCAT PEST CONTROL
        818,    // INTERNATIONAL INSECTICIDES
        787,    // KONE ASSARAIN LLC
        757,    // AG MELCO MUSCAT ELEVATOR LLC
        726,    // INTISAR FIRE AND SAFETY ENGINEERING LLC
        743,    // MARSA AL QURUM AGRICULTURE SERVICE AND TRAD LLC
        616,    // KEHLAN POOLS AND FOUNTAINS LLC
    ];

    /** OLD_AMC_VENDORS as an SQL list. */
    public static function oldAmcVendorList(): string
    {
        return implode(',', array_map('intval', self::OLD_AMC_VENDORS));
    }

    /** True for a database boolean (true / 't' / 1). */
    private static function flag($v): bool
    {
        return $v === true || $v === 't' || $v === 1 || $v === '1';
    }

    /**
     * The row one expense line belongs to, or null when it is not on the sheet
     * (cleaning bills, lease rent, municipal tax penalty, ...).
     * From AMC_CODES_FROM the account decides: AMC accounts -> AMC, A/C, fire alarm
     * and lift accounts -> Repair (a), Routine & Maintenance -> Repair (b).
     * Before it, A/C, fire alarm and lift bills from a sub-contractor are AMC and
     * the ones from a technician's service report are Repair (a); Routine &
     * Maintenance bills from OLD_AMC_VENDORS are AMC.
     */
    public static function rowFor($exp)
    {
        if (isset(self::BY_NAME[$exp->expense_name])) {
            return self::BY_NAME[$exp->expense_name];
        }
        $code    = (int) ($exp->acc_code ?? 0);
        $old     = self::flag($exp->before_cutover ?? false);
        $inhouse = self::flag($exp->inhouse ?? false);
        if (in_array($code, self::AMC_CODES, true)) {
            return self::AMC;
        }
        if (in_array($code, self::REPAIR_A_CODES, true)) {
            return ($old && !$inhouse) ? self::AMC : self::REPAIR_A;
        }
        if (in_array($code, self::REPAIR_B_CODES, true)) {
            return ($old && !$inhouse && self::flag($exp->old_amc_vendor ?? false)) ? self::AMC : self::REPAIR_B;
        }
        return null;
    }

    /** Municipal tax paid back by tenants who left early, collected this month (user, 04/10/2026). */
    public static function munTaxCollection(array $month): float
    {
        $sum = 0.0;
        foreach ($month['mun_tax'] ?? [] as $mt) {
            $sum += (float) $mt->amount;
        }
        return $sum;
    }

    /** One month's expense rows: [label => amount], in report order. */
    public static function expenseLines(array $month): array
    {
        $out = array_fill_keys(self::LABELS, 0.0);
        // Management fee only for buildings of Management Type Normal in the building master (user, 03/10/2026)
        $out['Management Fees']  = (int) ($month['building_management_id'] ?? 0) === 2 ? self::managementFee($month) : 0.0;
        $out['Cleaning Charges'] = (float) ($month['cleaning_charge'] ?? 0);
        foreach ($month['expenses'] ?? [] as $exp) {
            $row = self::rowFor($exp);
            if ($row !== null) {
                $out[$row] += (float) $exp->expense_amount;
            }
        }
        // VAT = 5% of Repair (b) + Management Fees + Cleaning Charges (user, 04/10/2026)
        $out['Value Added Tax (VAT)'] = round(($out[self::REPAIR_B] + $out['Management Fees'] + $out['Cleaning Charges']) * self::VAT_RATE, 3);
        return $out;
    }

    /**
     * One month's management fee from the landlord contract covering it: a fixed
     * amount (management_method 2), or a percentage of the month's income
     * (landlord_contract_percentage 1) or collection (2). Same formula as the
     * Landlord Tax Invoice.
     */
    public static function managementFee(array $month): float
    {
        $lc = $month['landlord_contract'] ?? null;
        if ($lc === null) {
            return 0.0;
        }
        if ((int) $lc->management_method === 2) {
            return (float) $lc->landlord_contract_management_fee;
        }
        $income = $collection = 0.0;
        foreach ($month['units'] ?? [] as $u) {
            $income     += (float) ($u->income_amount ?? 0);
            $collection += (float) ($u->collection_amount ?? 0);
        }
        foreach ($month['old_outstanding'] ?? [] as $u) {
            $collection += (float) ($u->collection_amount ?? 0);
        }
        $basis = (int) $lc->landlord_contract_percentage;
        if ($basis !== 1 && $basis !== 2) {
            return 0.0; // unknown basis on the contract: no fee rather than a guess
        }
        return round(($basis === 2 ? $collection : $income) * ((float) $lc->landlord_contract_management_fee / 100), 3);
    }

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
            $incomes[$m] = round($total);   // whole OMR on this sheet (user, 04/10/2026)
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
            $total += self::munTaxCollection($this->monthData[$m] ?? []);
            $collections[$m] = round($total);
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

        // Expense rows in the fixed order of the report (user, 03/10/2026):
        // name in col B, values in C-N, YTD in O. Only these rows make up Total Expenses.
        $expenseTotalsByMonth = array_fill(1, 12, 0);
        $linesByMonth = [];
        foreach (range(1, 12) as $m) {
            // Every amount in whole OMR; VAT from the rounded rows it is based on,
            // so the figures on the sheet add up (user, 04/10/2026)
            $lines = array_map('round', self::expenseLines($this->monthData[$m] ?? []));
            $lines['Value Added Tax (VAT)'] = round(($lines[self::REPAIR_B] + $lines['Management Fees'] + $lines['Cleaning Charges']) * self::VAT_RATE);
            $linesByMonth[$m] = $lines;
        }
        foreach (self::LABELS as $label) {
            $expenseRow = ['', $label];
            $yearTotal  = 0;
            foreach (range(1, 12) as $m) {
                $amount = (float) $linesByMonth[$m][$label];
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
            $overdues[$m] = round($total);
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
