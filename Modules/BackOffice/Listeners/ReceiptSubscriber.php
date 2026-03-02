<?php

namespace Modules\BackOffice\Listeners;

use Modules\BackOffice\Notifications\ReceiptNotification;
use Illuminate\Support\Facades\Notification;

 
class ReceiptSubscriber
{
    
    /**
    * Handle New Request  events.
    */
    public function onReceipt($event) {
		
    	Notification::send($event->users, new ReceiptNotification($event->receipt)); 
	}
        
     /**
    * Handle Reject  events.
    */
    public function onReject($event) {
         
        Notification::send($event->users, new ReceiptNotification($event->receipt)); 
    }
    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {

        $events->listen(
            'Modules\BackOffice\Events\ReceiptApprove',
            'Modules\BackOffice\Listeners\ReceiptSubscriber@onReceipt'
        );
         $events->listen(
            'Modules\BackOffice\Events\ReceiptReject',
            'Modules\BackOffice\Listeners\ReceiptSubscriber@onReject'
        );
 
    }   
    
}
