<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
        //URL::forceScheme('https');
        //URL::forceRootUrl('https://40d3-2804-14c-bb8c-8567-a4b3-6cf-5af6-6030.ngrok-free.app');
    }
}
