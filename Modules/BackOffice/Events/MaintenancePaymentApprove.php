<?php

namespace Modules\BackOffice\Events;

use Illuminate\Queue\SerializesModels;
use Modules\BackOffice\Entities\MaintenancePayment;
use App\User;

class MaintenancePaymentApprove
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(MaintenancePayment $maintenancePayment,$user)
    {
        $this->enquiry = $maintenancePayment;          
        $this->enquiry->text = "Maintenance Payment Approved  -  ".$maintenancePayment->maintenance_payment_no."  Added by ".\Auth::user()->username; 
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
