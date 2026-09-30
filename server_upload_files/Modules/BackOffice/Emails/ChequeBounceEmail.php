<?php
namespace Modules\BackOffice\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\BackOffice\Entities\Pdc;

class ChequeBounceEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Pdc $pdc)
    {
        $this->tenantName 	= $pdc->tenantContractInfo->tenant->tenant_name;
        $this->tenantemail  = $pdc->tenantContractInfo->tenant->tenant_contact_email ? $pdc->tenantContractInfo->tenant->tenant_contact_email:$pdc->tenantContractInfo->tenant->tenant_personal_email;
        $this->pdcCheckNo 	= $pdc->pdc_check_no;
        $this->pdcAmount    = number_format($pdc->pdc_amt, 3);
        $this->pdcDate      = $pdc->pdc_check_date ? date('d-m-Y', strtotime($pdc->pdc_check_date)) : '';
        $this->bounceReason = $pdc->pdc_bounce_reason ? $pdc->pdc_cancel_reason_name : '';
        $unit               = $pdc->tenantContractInfo->Unit->unit_no ?? '';
        $building           = $pdc->tenantContractInfo->building->building_name ?? '';
        $this->unitName     = trim($unit . ($unit !== '' && $building !== '' ? ', ' : '') . $building);
    }
    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        return $this->markdown('backoffice::Email.cheque_bounce_mail')->with(['tenantName'=>$this->tenantName,'tenantemail'=>$this->tenantemail,'pdcCheckNo'=>$this->pdcCheckNo,
                     'pdcAmount'=>$this->pdcAmount,'pdcDate'=>$this->pdcDate,'bounceReason'=>$this->bounceReason,'unitName'=>$this->unitName])->subject('Cheque Returned - Cheque No. ' . $this->pdcCheckNo);
    }
}
