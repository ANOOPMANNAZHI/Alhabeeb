<?php

namespace Modules\Maintenance\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Repair & Maintenance Report: one workbook per building, one sheet per month of the year.
 * $inHouse / $subContract are RepairMaintenanceReport::inHouse() / subContract() results (keyed by month).
 */
class RepairMaintenanceReportExport implements WithMultipleSheets
{
    protected $buildingName;
    protected $year;
    protected $months;
    protected $inHouse;
    protected $subContract;

    public function __construct($buildingName, $year, array $months, array $inHouse, array $subContract)
    {
        $this->buildingName = $buildingName;
        $this->year         = (int) $year;
        $this->months       = $months;
        $this->inHouse      = $inHouse;
        $this->subContract  = $subContract;
    }

    public function sheets(): array
    {
        $sheets = [];
        foreach ($this->months as $m) {
            $sheets[] = new RepairMaintenanceMonthSheet(
                $this->buildingName, $m, $this->year,
                isset($this->inHouse[$m]) ? $this->inHouse[$m] : [],
                isset($this->subContract[$m]) ? $this->subContract[$m] : []
            );
        }
        return $sheets;
    }
}
