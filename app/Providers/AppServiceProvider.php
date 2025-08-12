<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\WhatsAppService;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
public function register()
{
    $this->app->singleton(WhatsAppService::class, function ($app) {
        return new WhatsAppService();
    });
}

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
