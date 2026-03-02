<?php

namespace Modules\BackOffice\Events;

use Illuminate\Queue\SerializesModels;
use Modules\BackOffice\Entities\LandlordPayment;
use App\User;

class LandlordPaymentReject
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
        $this->enquiry->text = "Landlord Payment Rejected  -  ".$landlordPayment->landlord_payment_no."  Added by ".\Auth::user()->username; 
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
