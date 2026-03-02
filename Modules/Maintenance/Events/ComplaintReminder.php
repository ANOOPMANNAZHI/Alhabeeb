<?php

namespace Modules\Maintenance\Events;

use Illuminate\Queue\SerializesModels;
use Modules\Maintenance\Entities\ComplaintEnquiry;

class ComplaintReminder   
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
     public function __construct(ComplaintEnquiry $ComplaintEnquiry,$user)
    {
         $this->enquiry = $ComplaintEnquiry;
         /*$this->enquiry->text = " Complaint Reminder -  Call  ".$ComplaintEnquiry->complainer_name. " [".$ComplaintEnquiry->complaint_no."]"; */
         $this->enquiry->icon = 'fa-warning';
         $this->enquiry->icon_color = 'yellow';
         //$this->enquiry->href = url('tenantNextstage/'.$SalesEnquiry->id.'/101');
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
