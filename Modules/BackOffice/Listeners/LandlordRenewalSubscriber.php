<?php

namespace Modules\BackOffice\Listeners;


use Modules\BackOffice\Notifications\LandlordRenewalNotification ;
use Illuminate\Support\Facades\Notification;

 
class LandlordRenewalSubscriber
{
    
    /**
    * Handle New Enquiry  events.
    */
    public function onLandlordRenewalApprove($event) {
		
    	Notification::send($event->users, new LandlordRenewalNotification($event->enquiry)); 
	}
    /**
    * Handle New Enquiry  events.
    */
    public function onLandlordRenewalReject($event) {
        
        Notification::send($event->users, new LandlordRenewalNotification($event->enquiry)); 
    }
    /**
    * Handle New Enquiry  events.
    */
    public function onLandlordDueForRenewal($event) {
        
        Notification::send($event->users, new LandlordRenewalNotification($event->enquiry)); 
    }
      
    
    
    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        $events->listen(
            'Modules\BackOffice\Events\LandlordRenewalApprove',
            'Modules\BackOffice\Listeners\LandlordRenewalSubscriber@onLandlordRenewalApprove'
        );
        $events->listen(
            'Modules\BackOffice\Events\LandlordRenewalReject',
            'Modules\BackOffice\Listeners\LandlordRenewalSubscriber@onLandlordRenewalReject'
        );
        $events->listen(
            'Modules\BackOffice\Events\LandlordDueForRenewal',
            'Modules\BackOffice\Listeners\LandlordRenewalSubscriber@onLandlordDueForRenewal'
        );
        
       
    }   
    
    
    
}
