<?php

namespace Modules\Sales\Events;

use Illuminate\Queue\SerializesModels;
use Modules\Sales\Entities\SalesEnquiry;

class AssignedEnquiry    
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
         $this->enquiry->text = "Enquiry - ".$SalesEnquiry->sales_enquiry_no."  Assigned ";
         $this->enquiry->icon = 'fa-hand-o-right ';
         $this->enquiry->icon_color = 'yellow';
         $this->enquiry->href = url('tenantNextstage/'.$SalesEnquiry->id.'/'.$SalesEnquiry->work_flow_processes_code);
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
