<?php

namespace Modules\BackOffice\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

/**
 * Employee Tenant Contract report as a real .xlsx (08/10/2026: it used to send the HTML
 * table with an .xlsx name, which Excel refused to open).
 */
class EmployeeTenantContractReportExport implements FromView, ShouldAutoSize, WithTitle
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function view(): View
    {
        return view('backoffice::Reports.employee_tenant_contract_report_excel', $this->data);
    }

    public function title(): string
    {
        return 'Employee Tenant Contracts';
    }
}
