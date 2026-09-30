<?php
namespace Modules\BackOffice\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\BackOffice\Entities\Pdc;

class ChequeBounceAreEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Pdc $pdc, $greetName = null)
    {
        $this->tenantName 	= $pdc->tenantContractInfo->tenant->tenant_name;
        $this->pdcCheckNo 	= $pdc->pdc_check_no;
        $are_name = '';
        if(count($pdc->tenantContractInfo->building->buildingAssignTo) > 0)
                foreach($pdc->tenantContractInfo->building->buildingAssignTo as $assign)  
                  $are_name = $assign->buildingAssignToName->areUser->employee->employee_name??'';
        $this->areName = $greetName !== null ? $greetName : $are_name; // copies (e.g. finance) greet by their own name
        $this->buildingName = $pdc->tenantContractInfo->building->building_name;
        $this->bounceReason = $pdc->pdc_cancel_reason_name;   // from pdc_bounce_reason
        $this->bounceRemark = $pdc->bounce_remark;            // e.g. the bank's rejection reason; may be null
    }
    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        return $this->markdown('backoffice::Email.cheque_bounce_are_mail')->with(['tenantName'=>$this->tenantName,'areName'=>$this->areName,'pdcCheckNo'=>$this->pdcCheckNo,'buildingName'=>$this->buildingName,
                     'bounceReason'=>$this->bounceReason,'bounceRemark'=>$this->bounceRemark])->subject('Cheque Bounce');
    }
}
