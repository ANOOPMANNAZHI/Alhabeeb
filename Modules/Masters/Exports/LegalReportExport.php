<?php

namespace Modules\Masters\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

/** Excel sheet for the legal reports: the same Blade table the PDF uses (inline styles only). */
class LegalReportExport implements FromView, ShouldAutoSize, WithTitle
{
    protected $view;
    protected $data;
    protected $title;

    public function __construct($view, array $data, $title)
    {
        $this->view = $view;
        $this->data = $data;
        $this->title = $title;
    }

    public function view(): View
    {
        return view($this->view, $this->data);
    }

    public function title(): string
    {
        return $this->title;
    }
}
