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
    public function __construct(Pdc $pdc)
    {
        $this->tenantName 	= $pdc->tenantContractInfo->tenant->tenant_name;
        $this->pdcCheckNo 	= $pdc->pdc_check_no;
        if(count($pdc->tenantContractInfo->building->buildingAssignTo) > 0)
                foreach($pdc->tenantContractInfo->building->buildingAssignTo as $assign)  
                  $are_name = $assign->buildingAssignToName->areUser->employee->employee_name??'';
        $this->areName = $are_name;
        $this->buildingName = $pdc->tenantContractInfo->building->building_name;
    }
    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        return $this->markdown('backoffice::Email.cheque_bounce_are_mail')->with(['tenantName'=>$this->tenantName,'areName'=>$this->areName,'pdcCheckNo'=>$this->pdcCheckNo,'buildingName'=>$this->buildingName])->subject('Cheque Bounce');
    }
}
