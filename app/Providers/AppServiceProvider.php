<?php

namespace App\Providers;

use App\Services\SalesService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Регистрируем сервисы для dependency injection
        $this->app->singleton(SalesService::class, function ($app) {
            return new SalesService();
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
