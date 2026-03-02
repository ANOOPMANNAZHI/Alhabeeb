<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
         'Illuminate\Auth\Events\Login' => [
        'App\Listeners\LogSuccessfulLogin',
       ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
    
    /**
     * The subscriber classes to register.
     *
     * @var array
     */
    protected $subscribe = [
        'Modules\Sales\Listeners\EnquirySubscriber',
        'Modules\Maintenance\Listeners\ComplaintEnquirySubscriber',
        'Modules\Maintenance\Listeners\AmcSubscriber',
        'Modules\BackOffice\Listeners\TenantRenewalSubscriber',
        'Modules\BackOffice\Listeners\LandlordRenewalSubscriber',
        'Modules\BackOffice\Listeners\TenantTerminationSubscriber',
        'Modules\Masters\Listeners\LegalSubscriber',
        'Modules\BackOffice\Listeners\MaintenancePaymentSubscriber',
        'Modules\BackOffice\Listeners\LandlordPaymentSubscriber',
        'Modules\BackOffice\Listeners\ReceiptSubscriber',
        'Modules\BackOffice\Listeners\DepositRefundSubscriber',
        'Modules\BackOffice\Listeners\ChequeBounceSubscriber',
		'Modules\BackOffice\Listeners\TenantTerminationReferBackSubscriber',
    ];
    
    
}
