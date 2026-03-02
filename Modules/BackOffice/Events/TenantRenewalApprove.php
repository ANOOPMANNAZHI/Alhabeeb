<?php

namespace Modules\BackOffice\Events;

use Illuminate\Queue\SerializesModels;
use Modules\Sales\Entities\TenantContract;
use App\User;

class TenantRenewalApprove
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(TenantContract $TenantContract,$user)
    {
        $this->enquiry = $TenantContract;          
        $this->enquiry->text = "Tenant Renewal Approved  -  ".$TenantContract->tenant_contract_no."  Added by ".\Auth::user()->username; 
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
