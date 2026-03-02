<?php

namespace Modules\BackOffice\Listeners;


use Modules\BackOffice\Notifications\DepositRefundNotification ;
use Illuminate\Support\Facades\Notification;

 
class DepositRefundSubscriber
{
    
    /**
    * Handle New Enquiry  events.
    */
    public function onDepositRefundApprove($event) {
		
    	Notification::send($event->users, new DepositRefundNotification($event->enquiry)); 
	}
     /**
    * Handle New Enquiry  events.
    */
    public function onDepositRefundReject($event) {
        
        Notification::send($event->users, new DepositRefundNotification($event->enquiry)); 
    }
    
    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        $events->listen(
            'Modules\BackOffice\Events\DepositRefundApprove',
            'Modules\BackOffice\Listeners\DepositRefundSubscriber@onDepositRefundApprove'
        ); 
         $events->listen(
            'Modules\BackOffice\Events\DepositRefundReject',
            'Modules\BackOffice\Listeners\DepositRefundSubscriber@onDepositRefundReject'
        );     
    }   
    
    
    
}
