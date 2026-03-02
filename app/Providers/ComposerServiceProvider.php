<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class ComposerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
          // Using class based composers...
        View::composer(
            'sales::enquiry_form', 'App\Http\ViewComposers\AddEnquiryComposer'
        );
        View::composer(
            'maintenance::complaint_form', 'App\Http\ViewComposers\AddEnquiryComposer'
        );
        View::composer(
            'sales::edit_sale_enquiry_modal', 'App\Http\ViewComposers\AddEnquiryComposer'
        );
        
        View::composer(
            'layouts.notification', 'App\Http\ViewComposers\NotificationComposer'
        );
        
        
         View::composer(
            'dashboard.notification', 'App\Http\ViewComposers\NotificationComposer'
        );
        
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
