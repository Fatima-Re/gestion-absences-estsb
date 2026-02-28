<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Setting;

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
        // Register mail view path
        \Illuminate\Support\Facades\View::addNamespace(
            'mail',
            resource_path('views/vendor/mail')
        );
    }
}
