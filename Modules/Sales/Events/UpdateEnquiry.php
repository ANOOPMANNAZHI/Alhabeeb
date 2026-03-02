<?php

namespace Modules\Sales\Events;

use Illuminate\Queue\SerializesModels;
use Modules\Sales\Entities\SalesEnquiry;

class UpdateEnquiry   
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
     public function __construct(SalesEnquiry $SalesEnquiry,$user)
    {
         $this->enquiry = $SalesEnquiry;
         
          if($this->enquiry->sales_type == 1)
           $type = 'Tenant';
          else 
           $type = 'Landlord';
           
         $this->enquiry->text = $type." Enquiry  - ".$SalesEnquiry->sales_enquiry_no."  Updated by ".\Auth::user()->username; 
         $this->enquiry->icon = 'fa-pencil-square-o';
         $this->enquiry->icon_color = 'purple-bgcolor';
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
