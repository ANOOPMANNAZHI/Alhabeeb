<?php

namespace Modules\Sales\Events;

use Illuminate\Queue\SerializesModels;
use Modules\Sales\Entities\SalesEnquiry;

class ReminderAssignEnquiry   
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
         $this->enquiry->text = " Reminder - Call  ".$SalesEnquiry->sales_enquiry_name. " [".$SalesEnquiry->sales_enquiry_no."]"; 
         $this->users = $user;
         $this->enquiry->icon = 'fa-hand-o-right ';
         $this->enquiry->icon_color = 'red';
         $this->enquiry->href = url('tenantNextstage/'.$SalesEnquiry->id.'/102');
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
