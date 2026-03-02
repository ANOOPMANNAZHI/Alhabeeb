<?php

namespace Modules\Maintenance\Listeners;


use Modules\Maintenance\Notifications\AmcNotification ;
use Illuminate\Support\Facades\Notification;

 
class AmcSubscriber
{
    

    /**
    * Handle New Enquiry  events.
    */
    public function onAmcTaskReminder($event) {
        
        Notification::send($event->users, new AmcNotification($event->enquiry)); 
    }
    
        
    
    
    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        $events->listen(
            'Modules\Maintenance\Events\AmcTaskReminder',
            'Modules\Maintenance\Listeners\AmcSubscriber@onAmcTaskReminder'
        );
        
       
    }   
    
    
    
}