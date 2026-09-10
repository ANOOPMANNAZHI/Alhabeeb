<?php

namespace Modules\BackOffice\Pdf;

class DepositRentReportV2Pdf extends \FPDF
{
    public function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', '', 8);
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . ' of {nb}', 0, 0, 'C');
    }
}
