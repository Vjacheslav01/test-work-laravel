<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SalesFilterRequest;
use App\Http\Resources\SaleResource;
use App\Services\SalesService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @group Sales
 *
 * API для работы с продажами
 */
class SalesController extends Controller
{
    public function __construct(
        private readonly SalesService $salesService
    ) {}

    /**
     * Получение списка продаж с фильтрами и пагинацией
     *
     * @param SalesFilterRequest $request
     * @return AnonymousResourceCollection
     */
    public function index(SalesFilterRequest $request): AnonymousResourceCollection
    {
        try {
            $sales = $this->salesService->getSalesWithFilters($request);
            
            return SaleResource::collection($sales)->additional([
                'meta' => [
                    'filters_applied' => $request->only(['dateFrom', 'dateTo', 'category']),
                    'available_categories' => $this->salesService->getAvailableCategories(),
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching sales data', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            return response()->json([
                'error' => 'Ошибка получения данных о продажах',
                'message' => app()->isProduction() ? 'Внутренняя ошибка сервера' : $e->getMessage()
            ], 500);
        }
    }

    /**
     * Получение данных для графика
     *
     * @param SalesFilterRequest $request
     * @return JsonResponse
     */
    public function getChartData(SalesFilterRequest $request): JsonResponse
    {
        try {
            $chartData = $this->salesService->getChartData($request);
            
            return response()->json([
                'success' => true,
                'chartData' => $chartData,
                'meta' => [
                    'filters_applied' => $request->only(['dateFrom', 'dateTo', 'category']),
                    'total_points' => count($chartData['data'])
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching chart data', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Ошибка получения данных для графика',
                'message' => app()->isProduction() ? 'Внутренняя ошибка сервера' : $e->getMessage()
            ], 500);
        }
    }

    /**
     * Получение списка доступных категорий
     *
     * @return JsonResponse
     */
    public function getCategories(): JsonResponse
    {
        try {
            $categories = $this->salesService->getAvailableCategories();
            
            return response()->json([
                'success' => true,
                'categories' => $categories
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching categories', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Ошибка получения категорий',
                'message' => app()->isProduction() ? 'Внутренняя ошибка сервера' : $e->getMessage()
            ], 500);
        }
    }
} 