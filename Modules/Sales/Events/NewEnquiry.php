<?php

namespace Modules\Sales\Events;

use Illuminate\Queue\SerializesModels;
use Modules\Sales\Entities\SalesEnquiry;
use App\User;

class NewEnquiry
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
         
         if($this->enquiry->sales_type == 1){
            $type = 'Tenant';
            $this->enquiry->href = url('tenantNextstage/'.$SalesEnquiry->id.'/101');
         }else {
            $type = 'Landlord';
            $this->enquiry->href = url('landlordNextstage/'.$SalesEnquiry->id.'/201');
         }
           
           
         $this->enquiry->text = "New ".$type. "  Enquiry  -  ".$SalesEnquiry->sales_enquiry_no."  Added by ".\Auth::user()->username; 
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
