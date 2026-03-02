<?php

namespace Modules\BackOffice\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class MonthlyTenancyReportExport implements FromView, ShouldAutoSize, WithTitle
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function view(): View
    {
        return view('backoffice::Reports.monthly_tenancy_report_excel', $this->data);
    }

    public function title(): string
    {
        return 'Monthly Tenancy Report';
    }
}
