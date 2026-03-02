<?php

namespace Modules\Masters\Listeners;


use Modules\Masters\Notifications\LegalNotification ;
use Illuminate\Support\Facades\Notification;

 
class LegalSubscriber
{
    
    /**
    * Handle New Enquiry  events.
    */
    public function onLegalApprove($event) {
		
    	Notification::send($event->users, new LegalNotification($event->enquiry)); 
	}
    /**
    * Handle New Enquiry  events.
    */
    public function onLegalReferBack($event) {
        
        Notification::send($event->users, new LegalNotification($event->enquiry)); 
    }
    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        $events->listen(
            'Modules\Masters\Events\LegalApprove',
            'Modules\Masters\Listeners\LegalSubscriber@onLegalApprove'
        );  
        $events->listen(
            'Modules\Masters\Events\LegalReferBack',
            'Modules\Masters\Listeners\LegalSubscriber@onLegalReferBack'
        );    
    }   
    
    
    
}
