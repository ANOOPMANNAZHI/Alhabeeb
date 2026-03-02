<?php

namespace Modules\BackOffice\Events;

use Illuminate\Queue\SerializesModels;
use Modules\BackOffice\Entities\MaintenancePayment;
use App\User;

class MaintenancePaymentReject
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
        $this->enquiry->text = "Maintenance Payment Rejected  -  ".$maintenancePayment->maintenance_payment_no."  Added by ".\Auth::user()->username; 
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
