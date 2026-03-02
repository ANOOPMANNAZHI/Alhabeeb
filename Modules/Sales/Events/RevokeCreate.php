<?php

namespace Modules\Sales\Events;

use Illuminate\Queue\SerializesModels;
use Modules\Sales\Entities\TenantContract;

class RevokeCreate
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
     public function __construct(TenantContract $TenantContract,$user =null)
    {
		 $this->enquiry        = $TenantContract;
         $this->users         = $user;

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
