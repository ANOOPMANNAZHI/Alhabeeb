<?php

namespace Modules\BackOffice\Listeners;


use Modules\BackOffice\Notifications\MaintenancePaymentNotification ;
use Illuminate\Support\Facades\Notification;

 
class MaintenancePaymentSubscriber
{
    
    /**
    * Handle New Enquiry  events.
    */
    public function onMaintenancePaymentApprove($event) {
		
    	Notification::send($event->users, new MaintenancePaymentNotification($event->enquiry)); 
	}
     /**
    * Handle New Enquiry  events.
    */
    public function onMaintenancePaymentReject($event) {
        
        Notification::send($event->users, new MaintenancePaymentNotification($event->enquiry)); 
    }
    
    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        $events->listen(
            'Modules\BackOffice\Events\MaintenancePaymentApprove',
            'Modules\BackOffice\Listeners\MaintenancePaymentSubscriber@onMaintenancePaymentApprove'
        ); 
         $events->listen(
            'Modules\BackOffice\Events\MaintenancePaymentReject',
            'Modules\BackOffice\Listeners\MaintenancePaymentSubscriber@onMaintenancePaymentReject'
        );     
    }   
    
    
    
}
