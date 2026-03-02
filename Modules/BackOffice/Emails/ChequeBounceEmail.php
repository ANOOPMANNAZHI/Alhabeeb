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
    }
    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        return $this->markdown('backoffice::Email.cheque_bounce_mail')->with(['tenantName'=>$this->tenantName,'tenantemail'=>$this->tenantemail,'pdcCheckNo'=>$this->pdcCheckNo])->subject('Cheque Bounce');
    }
}
