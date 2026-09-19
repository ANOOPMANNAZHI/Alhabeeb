<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Legacy views/controllers rely on PHP 7 notice semantics
        \App\Support\Php7NoticeCompat::register();

        //SideMenu
        view()->composer('layouts.sidebar',function($view){
            $view->with('sideMenu',\App\Http\Controllers\HomeController::menuListing());
        });
      
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Legacy activity()->withProperties($model) calls (see LenientActivityLogger)
        $this->app->bind(\Spatie\Activitylog\Support\ActivityLogger::class, \App\Support\LenientActivityLogger::class);
    }
}
