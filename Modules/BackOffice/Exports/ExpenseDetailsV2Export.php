<?php

namespace Modules\BackOffice\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class ExpenseDetailsV2Export implements FromView, ShouldAutoSize, WithTitle
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function view(): View
    {
        $viewName = $this->data['reportType'] == 1
            ? 'backoffice::Reports.expense_details_summary_v2_excel'
            : 'backoffice::Reports.expense_details_v2_excel';

        return view($viewName, $this->data);
    }

    public function title(): string
    {
        return $this->data['reportType'] == 1 ? 'Expense Details Summary v2' : 'Expense Details v2';
    }
}
