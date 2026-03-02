<?php

namespace Modules\BackOffice\Events;

use Illuminate\Queue\SerializesModels;
use Modules\BackOffice\Entities\DepositRefund;
use App\User;

class DepositRefundApprove
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(DepositRefund $depositRefund,$user)
    {
        $this->enquiry = $depositRefund;          
        $this->enquiry->text = "Deposit Refund Approved  -  ".$depositRefund->deposit_refund_no."  Added by ".\Auth::user()->username; 
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
