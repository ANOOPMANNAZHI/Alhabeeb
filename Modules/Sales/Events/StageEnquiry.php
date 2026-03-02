<?php

namespace Modules\Sales\Events;

use Illuminate\Queue\SerializesModels;
use Modules\Sales\Entities\SalesEnquiry;

class StageEnquiry   
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
         //dd($user);
           
         $this->enquiry->text =   $this->enquiry->msg."  Enquiry  -  ".$SalesEnquiry->sales_enquiry_no;
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
