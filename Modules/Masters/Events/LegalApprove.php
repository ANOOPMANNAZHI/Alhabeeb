<?php

namespace Modules\Masters\Events;

use Illuminate\Queue\SerializesModels;
use Modules\Masters\Entities\Legal;
use App\User;

class LegalApprove
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Legal $legal,$user)
    {
        $this->enquiry = $legal;          
        $this->enquiry->text = "Legal Case -  ".$legal->content. " ".$legal->tenantContract->tenant_contract_no."  Added by ".\Auth::user()->username; 
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
