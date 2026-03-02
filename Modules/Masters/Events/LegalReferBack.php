<?php

namespace Modules\Masters\Events;

use Illuminate\Queue\SerializesModels;
use Modules\Masters\Entities\Legal;
use App\User;

class LegalReferBack
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
        $this->enquiry->text = "Legal Case Refer Back  -  ".$legal->tenantContract->tenant_contract_no."  Added by ".\Auth::user()->username; 
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
