<?php

namespace Modules\Sales\Listeners;

use Modules\Sales\Notifications\EnquiryNotification ;
use Modules\Sales\Notifications\TenantContractNotification ;
use Illuminate\Support\Facades\Notification;

 
class EnquirySubscriber
{
    
     /**
     * Handle New Enquiry  events.
     */
    public function onNewEnquiry($event) {
		
    	Notification::send($event->users, new EnquiryNotification($event->enquiry)); 
	 }
    
    
     /**
     * Handle Update Enquiry  events.
     */
    
    public function onUpdateEnquiry($event) {
		
    	Notification::send($event->users, new EnquiryNotification($event->enquiry)); 
	} 
	
	
	/**
    * Handle Reminder Enquiry  events.
    */
    
    public function onReminderEnquiry($event) {
		
    	Notification::send($event->users, new EnquiryNotification($event->enquiry)); 
	}       
    
    
	/**
    * Handle Assigned Enquiry  events.
    */
    
    public function onAssignedEnquiry($event) {
		
    	Notification::send($event->users, new EnquiryNotification($event->enquiry)); 
	}       
    
    
	/**
    * Handle ReminderAssign Enquiry  events.
    */
    
    public function onReminderAssignEnquiry($event) {
		
    	Notification::send($event->users, new EnquiryNotification($event->enquiry)); 
	}       
    
    
    
	/**
    * Handle Stage Enquiry  events.
    */
    
    public function onStageEnquiry($event) {
		
    	Notification::send($event->users, new EnquiryNotification($event->enquiry)); 
	}       
    /**
    * Handle Stage Enquiry  events.
    */
    
    public function onRevokeEnquiry($event) {
		
    	Notification::send($event->users, new EnquiryNotification($event->enquiry)); 
	}
    /**
    * Handle Stage Enquiry  Create.
    */
    
    public function onRevokeCreate($event) {
        
        Notification::send($event->users, new TenantContractNotification($event->enquiry)); 
    }
    
    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        $events->listen(
            'Modules\Sales\Events\NewEnquiry',
            'Modules\Sales\Listeners\EnquirySubscriber@onNewEnquiry'
        );
        
        
        $events->listen(
            'Modules\Sales\Events\UpdateEnquiry',
            'Modules\Sales\Listeners\EnquirySubscriber@onUpdateEnquiry'
        );
        
         $events->listen(
            'Modules\Sales\Events\ReminderEnquiry',
            'Modules\Sales\Listeners\EnquirySubscriber@onReminderEnquiry'
        );
       
         $events->listen(
            'Modules\Sales\Events\AssignedEnquiry',
            'Modules\Sales\Listeners\EnquirySubscriber@onAssignedEnquiry'
        );
       
       
        $events->listen(
            'Modules\Sales\Events\ReminderAssignEnquiry',
            'Modules\Sales\Listeners\EnquirySubscriber@onReminderAssignEnquiry'
        );
        
        $events->listen(
            'Modules\Sales\Events\StageEnquiry',
            'Modules\Sales\Listeners\EnquirySubscriber@onStageEnquiry'
        );
        $events->listen(
            'Modules\Sales\Events\RevokeEnquiry',
            'Modules\Sales\Listeners\EnquirySubscriber@onRevokeEnquiry'
        );
        $events->listen(
            'Modules\Sales\Events\RevokeCreate',
            'Modules\Sales\Listeners\EnquirySubscriber@onRevokeCreate'
        );
       
    }   
    
    
    
}
