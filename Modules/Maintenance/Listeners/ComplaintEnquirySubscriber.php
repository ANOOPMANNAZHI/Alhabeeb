<?php

namespace Modules\Maintenance\Listeners;


use Modules\Maintenance\Notifications\ComplaintEnquiryNotification ;
use Illuminate\Support\Facades\Notification;

 
class ComplaintEnquirySubscriber
{
    
    /**
    * Handle New Enquiry  events.
    */
    public function onNewComplaintEnquiry($event) {
		
    	Notification::send($event->users, new ComplaintEnquiryNotification($event->enquiry)); 
	}
    /**
    * Handle New Enquiry  events.
    */
    public function onComplaintReminder($event) {
        
        Notification::send($event->users, new ComplaintEnquiryNotification($event->enquiry)); 
    }
    /**
    * Handle New Enquiry  events.
    */
    public function onComplaintAssigned($event) {
        
        Notification::send($event->users, new ComplaintEnquiryNotification($event->enquiry)); 
    }
        
    
    
    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        $events->listen(
            'Modules\Maintenance\Events\NewComplaintEnquiry',
            'Modules\Maintenance\Listeners\ComplaintEnquirySubscriber@onNewComplaintEnquiry'
        );
        $events->listen(
            'Modules\Maintenance\Events\ComplaintReminder',
            'Modules\Maintenance\Listeners\ComplaintEnquirySubscriber@onComplaintReminder'
        );
        $events->listen(
            'Modules\Maintenance\Events\ComplaintAssigned',
            'Modules\Maintenance\Listeners\ComplaintEnquirySubscriber@onComplaintAssigned'
        );
        
       
    }   
    
    
    
}
