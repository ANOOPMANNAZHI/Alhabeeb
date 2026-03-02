<?php

namespace Modules\BackOffice\Listeners;


use Modules\BackOffice\Notifications\LandlordPaymentNotification ;
use Illuminate\Support\Facades\Notification;

 
class LandlordPaymentSubscriber
{
    
    /**
    * Handle New Enquiry  events.
    */
    public function onLandlordPaymentApprove($event) {
		
    	Notification::send($event->users, new LandlordPaymentNotification($event->enquiry)); 
	}
     /**
    * Handle New Enquiry  events.
    */
    public function onLandlordPaymentReject($event) {
        
        Notification::send($event->users, new LandlordPaymentNotification($event->enquiry)); 
    }
    
    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        $events->listen(
            'Modules\BackOffice\Events\LandlordPaymentApprove',
            'Modules\BackOffice\Listeners\LandlordPaymentSubscriber@onLandlordPaymentApprove'
        ); 
         $events->listen(
            'Modules\BackOffice\Events\LandlordPaymentReject',
            'Modules\BackOffice\Listeners\LandlordPaymentSubscriber@onLandlordPaymentReject'
        );     
    }   
    
    
    
}
