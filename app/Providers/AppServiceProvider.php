<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

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
        // Jika diakses melalui ngrok atau tunnel HTTPS, paksa skema HTTPS agar asset CSS/JS tidak terblokir (Mixed Content)
        if (
            (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
            (request()->server('HTTP_X_FORWARDED_PROTO') === 'https') ||
            str_contains(request()->getHost(), 'ngrok')
        ) {
            URL::forceScheme('https');
        }
    }
}
