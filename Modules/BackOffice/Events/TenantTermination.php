<?php

namespace Modules\BackOffice\Events;

use Illuminate\Queue\SerializesModels;
use Modules\Sales\Entities\TenantContract;
use Modules\BackOffice\Entities\Termination;

class TenantTermination  
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
     public function __construct(Termination $termination,$user)
    {
         $this->enquiry = $termination;
         /*$this->enquiry->text = "Tenant Renewal Rejected  -  ".$TenantContract->tenant_contract_no."  Added by ".\Auth::user()->username;*/ 
         $this->enquiry->icon = 'fa-warning';
         $this->enquiry->icon_color = 'yellow';
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
