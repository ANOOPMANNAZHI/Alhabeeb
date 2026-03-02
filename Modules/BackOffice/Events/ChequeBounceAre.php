<?php

namespace Modules\BackOffice\Events;

use Illuminate\Queue\SerializesModels;
use Modules\BackOffice\Entities\Pdc;
use App\User;

class ChequeBounceAre
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Pdc $pdc,$user)
    {
        $this->enquiry = $pdc;          
        $this->enquiry->text = "Pdc Cheque Bounced  -  ".$pdc->pdc_check_no." for the Contract ".$pdc->tenantContractInfo->tenant_contract_no."  Added by ".\Auth::user()->username; 
        $this->enquiry->icon = 'fa-envelope-o';
        $this->enquiry->icon_color = 'blue-bgcolor';
        
        $this->users = $user;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return [];
    }
}


