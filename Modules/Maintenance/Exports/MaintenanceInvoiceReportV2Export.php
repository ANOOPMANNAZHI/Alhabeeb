<?php

namespace Modules\Maintenance\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class MaintenanceInvoiceReportV2Export implements FromView, ShouldAutoSize, WithTitle
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function view(): View
    {
        return view('maintenance::reports.maintenance_invoice_report_v2_excel', $this->data);
    }

    public function title(): string
    {
        return 'Maintenance Invoice Report v2';
    }
}
