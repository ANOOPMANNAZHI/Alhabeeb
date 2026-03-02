<?php

namespace Modules\BackOffice\Events;

use Illuminate\Queue\SerializesModels;
use Modules\Sales\Entities\LandlordContract;

class LandlordRenewalReject   
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
     public function __construct(LandlordContract $LandlordContract,$user)
    {
         $this->enquiry = $LandlordContract;
         $this->enquiry->text = "Landlord Renewal Rejected  -  ".$LandlordContract->landlord_contract_no."  Added by ".\Auth::user()->username; 
         $this->enquiry->icon = 'fa-warning';
         $this->enquiry->icon_color = 'yellow';
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
