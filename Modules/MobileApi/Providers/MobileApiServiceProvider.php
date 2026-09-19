<?php

namespace Modules\MobileApi\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class MobileApiServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../Config/config.php', 'mobileapi');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'mobileapi');
        require __DIR__ . '/../Routes/breadcrumbs.php';

        Route::aliasMiddleware('mobile.enabled', \Modules\MobileApi\Http\Middleware\EnsureMobileApiEnabled::class);
        Route::aliasMiddleware('mobile.device', \Modules\MobileApi\Http\Middleware\EnsureRegisteredDevice::class);
    }

    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
    }
}
