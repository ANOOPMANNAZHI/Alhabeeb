<?php

namespace Modules\BackOffice\Listeners;


use Modules\BackOffice\Notifications\TenantRenewalNotification ;
use Illuminate\Support\Facades\Notification;

 
class TenantRenewalSubscriber
{
    
    /**
    * Handle New Enquiry  events.
    */
    public function onTenantRenewalApprove($event) {
		
    	Notification::send($event->users, new TenantRenewalNotification($event->enquiry)); 
	}
    /**
    * Handle New Enquiry  events.
    */
    public function onTenantRenewalReject($event) {
        
        Notification::send($event->users, new TenantRenewalNotification($event->enquiry)); 
    }
    
      
    
    
    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        $events->listen(
            'Modules\BackOffice\Events\TenantRenewalApprove',
            'Modules\BackOffice\Listeners\TenantRenewalSubscriber@onTenantRenewalApprove'
        );
        $events->listen(
            'Modules\BackOffice\Events\TenantRenewalReject',
            'Modules\BackOffice\Listeners\TenantRenewalSubscriber@onTenantRenewalReject'
        );
        
        
       
    }   
    
    
    
}
