<?php

namespace Modules\BackOffice\Events;

use Illuminate\Queue\SerializesModels;
use Modules\BackOffice\Entities\LandlordPayment;
use App\User;

class LandlordPaymentApprove
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(LandlordPayment $landlordPayment,$user)
    {
        $this->enquiry = $landlordPayment;          
        $this->enquiry->text = "Landlord Payment Approved  -  ".$landlordPayment->landlord_payment_no."  Added by ".\Auth::user()->username; 
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
