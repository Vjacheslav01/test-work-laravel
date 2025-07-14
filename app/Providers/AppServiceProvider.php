<?php

namespace App\Providers;

use App\Services\ExcelImportService;
use App\Services\SalesService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Регистрируем сервисы для dependency injection (для работы с продажами)
        $this->app->singleton(SalesService::class, function ($app) {
            return new SalesService();
        });

        // Регистрируем сервисы для dependency injection (для загрузки товаров через excel)
        $this->app->singleton(ExcelImportService::class, function ($app) {
            return new ExcelImportService();
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
