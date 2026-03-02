<?php

namespace Modules\Sales\Events;

use Illuminate\Queue\SerializesModels;
use Modules\Sales\Entities\SalesEnquiry;

class RevokeEnquiry   
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
         $this->enquiry->text =   "Revoke Rejected";
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
