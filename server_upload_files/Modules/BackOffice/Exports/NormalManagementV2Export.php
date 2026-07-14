<?php

namespace Modules\BackOffice\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class NormalManagementV2Export implements WithMultipleSheets
{
    protected $monthData;
    protected $year;
    protected $buildingName;
    protected $selectedMonth; // 'all' or int 1-12

    public function __construct($monthData, $year, $buildingName, $selectedMonth = 'all')
    {
        $this->monthData     = $monthData;
        $this->year          = $year;
        $this->buildingName  = $buildingName;
        $this->selectedMonth = $selectedMonth;
    }

    public function sheets(): array
    {
        // Single month selected — one sheet only
        if ($this->selectedMonth !== 'all') {
            $m = (int)$this->selectedMonth;
            return [
                new NormalManagementMonthSheet(
                    $this->monthData[$m] ?? ['units' => [], 'expenses' => [], 'occupancy' => []],
                    $m,
                    $this->year,
                    $this->buildingName
                ),
            ];
        }

        // All months — 12 month sheets + Consolidate
        $sheets = [];
        foreach (range(1, 12) as $m) {
            $sheets[] = new NormalManagementMonthSheet(
                $this->monthData[$m] ?? ['units' => [], 'expenses' => [], 'occupancy' => []],
                $m,
                $this->year,
                $this->buildingName
            );
        }
        $sheets[] = new NormalManagementConsolidateSheet(
            $this->monthData,
            $this->year,
            $this->buildingName
        );
        return $sheets;
    }
}
