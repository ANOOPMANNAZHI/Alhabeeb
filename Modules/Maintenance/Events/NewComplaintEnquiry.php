<?php

namespace Modules\Maintenance\Events;

use Illuminate\Queue\SerializesModels;
use Modules\Maintenance\Entities\ComplaintEnquiry;
use App\User;

class NewComplaintEnquiry
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
        /*$this->enquiry->href = url('tenantNextstage/'.$ComplaintEnquiry->id.'/101');*/           
        $this->enquiry->text = "New Complaint Enquiry  -  ".$ComplaintEnquiry->complaint_no."  Added by ".\Auth::user()->username; 
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
