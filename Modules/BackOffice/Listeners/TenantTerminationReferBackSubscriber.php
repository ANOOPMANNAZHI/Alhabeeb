<?php

namespace Modules\BackOffice\Listeners;


use Modules\BackOffice\Notifications\TenantTerminationReferBackNotification ;
use Illuminate\Support\Facades\Notification;

 
class TenantTerminationReferBackSubscriber
{
    
    /**
    * Handle New Enquiry  events.
    */
    public function onTenantTerminationReferBack($event) {
		
    	Notification::send($event->users, new TenantTerminationReferBackNotification($event->termination)); 
	}
    
    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        $events->listen(
            'Modules\BackOffice\Events\TenantTerminationReferBack',
            'Modules\BackOffice\Listeners\TenantTerminationReferBackSubscriber@onTenantTerminationReferBack'
        );    
    }   
    
    
    
}
