<?php

namespace Modules\BackOffice\Listeners;


use Modules\BackOffice\Notifications\ChequeBounceNotification ;
use Illuminate\Support\Facades\Notification;

 
class ChequeBounceSubscriber
{
    
    /**
    * Handle New Enquiry  events.
    */
    public function onChequeBounce($event) {
		
    	Notification::send($event->users, new ChequeBounceNotification($event->enquiry)); 
	}
    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        $events->listen(
            'Modules\BackOffice\Events\ChequeBounceAre',
            'Modules\BackOffice\Listeners\ChequeBounceSubscriber@onChequeBounce'
        );   
    }   
    
    
    
}
