<?php

namespace Modules\Sales\Events;

use Illuminate\Queue\SerializesModels;
use Modules\Sales\Entities\SalesEnquiry;

class ReminderEnquiry   
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
         
          
           
         $this->enquiry->text = " Reminder -  Call  ".$SalesEnquiry->sales_enquiry_name. " [".$SalesEnquiry->sales_enquiry_no."]"; 
         $this->enquiry->icon = 'fa-warning';
         $this->enquiry->icon_color = 'yellow';
         $this->enquiry->href = url('tenantNextstage/'.$SalesEnquiry->id.'/101');
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
