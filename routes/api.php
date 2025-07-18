<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SalesController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Для SPA аутентификации с Sanctum все маршруты должны использовать 
| web middleware для правильной работы с сессиями и CSRF токенами.
|
*/

// Получение CSRF cookie для SPA
Route::get('/sanctum/csrf-cookie', function (Request $request) {
    return response()->json(['message' => 'CSRF cookie set']);
});

// Все маршруты для SPA используют web middleware
Route::middleware(['web'])->group(function () {
    
    // Публичные маршруты аутентификации
    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/register', [AuthController::class, 'register']);
    });

    // Защищенные маршруты
    Route::middleware(['auth:sanctum'])->group(function () {
        
        // Маршруты аутентификации
        Route::prefix('auth')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::get('/user', [AuthController::class, 'user']);
        });

        // API для работы с продажами
        Route::prefix('sales')->group(function () {
            Route::get('/', [SalesController::class, 'index']);
            Route::get('/chart', [SalesController::class, 'getChartData']);
            Route::get('/categories', [SalesController::class, 'getCategories']);
            
            // Excel импорт
            Route::post('/import', [SalesController::class, 'importExcel']);
            Route::post('/validate-excel', [SalesController::class, 'validateExcel']);
            Route::get('/template', [SalesController::class, 'downloadTemplate']);
        });
    });
}); 