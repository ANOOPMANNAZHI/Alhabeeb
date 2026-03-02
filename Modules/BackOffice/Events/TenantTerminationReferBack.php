<?php

namespace Modules\BackOffice\Events;

use Illuminate\Queue\SerializesModels;
use Modules\BackOffice\Entities\Termination;
use App\User;

class TenantTerminationReferBack
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Termination $termination,$user)
    {
        $this->termination = $termination;          
        $this->termination->text = "Tenant Termination ReferBack  -  ".$termination->tenantContract->tenant_contract_no."  Added by ".\Auth::user()->username; 
        $this->termination->icon = 'fa-envelope-o';
        $this->termination->icon_color = 'blue-bgcolor';
        
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


