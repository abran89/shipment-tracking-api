<?php

namespace App\Providers;

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
        if (app()->environment('production')) {
            if (empty(config('services.carrier_webhook_secret'))) {
                throw new \RuntimeException('La variable CARRIER_WEBHOOK_SECRET no está configurada en .env');
            }

            if (empty(config('services.webhook_url'))) {
                throw new \RuntimeException('La variable WEBHOOK_URL no está configurada en .env');
            }
        }
    }
}
