<?php

namespace Modules\BackOffice\Listeners;


use Modules\BackOffice\Notifications\TenantTerminationNotification ;
use Illuminate\Support\Facades\Notification;

 
class TenantTerminationSubscriber
{
    
    /**
    * Handle New Enquiry  events.
    */
    public function onTenantTermination($event) {
		
    	Notification::send($event->users, new TenantTerminationNotification($event->enquiry)); 
	}
        
    
    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        $events->listen(
            'Modules\BackOffice\Events\TenantTermination',
            'Modules\BackOffice\Listeners\TenantTerminationSubscriber@onTenantTermination'
        );
        
        
        
       
    }   
    
    
    
}
