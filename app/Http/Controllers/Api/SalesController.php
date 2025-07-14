<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExcelImportRequest;
use App\Http\Requests\SalesFilterRequest;
use App\Http\Resources\SaleResource;
use App\Services\ExcelImportService;
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
        private readonly SalesService $salesService,
        private readonly ExcelImportService $excelImportService
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

    /**
     * Импорт продаж из Excel файла
     *
     * @param ExcelImportRequest $request
     * @return JsonResponse
     */
    public function importExcel(ExcelImportRequest $request): JsonResponse
    {
        try {
            $result = $this->excelImportService->importSalesFile($request->file('file'));
            
            $status = $result['success'] ? 200 : 422;
            
            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'stats' => $result['stats'] ?? null,
                'errors' => $result['stats']['errors'] ?? []
            ], $status);
            
        } catch (\Exception $e) {
            \Log::error('Error importing Excel file', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file_name' => $request->file('file')->getClientOriginalName()
            ]);

            return response()->json([
                'success' => false,
                'message' => app()->isProduction() 
                    ? 'Ошибка при импорте файла' 
                    : 'Ошибка при импорте файла: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Скачивание шаблона Excel файла
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|JsonResponse
     */
    public function downloadTemplate()
    {
        try {
            $filePath = $this->excelImportService->generateTemplate();
            
            return response()->download($filePath, 'sales_import_template.xlsx')->deleteFileAfterSend();
            
        } catch (\Exception $e) {
            \Log::error('Error generating Excel template', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ошибка при создании шаблона',
                'error' => app()->isProduction() ? 'Внутренняя ошибка сервера' : $e->getMessage()
            ], 500);
        }
    }

    /**
     * Валидация структуры Excel файла
     *
     * @param ExcelImportRequest $request
     * @return JsonResponse
     */
    public function validateExcel(ExcelImportRequest $request): JsonResponse
    {
        try {
            $result = $this->excelImportService->validateFileStructure($request->file('file'));
            
            return response()->json([
                'success' => $result['valid'],
                'message' => $result['message'],
                'missing_headers' => $result['missing_headers'] ?? [],
                'expected_headers' => $result['expected_headers'] ?? []
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error validating Excel file', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file_name' => $request->file('file')->getClientOriginalName()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ошибка при валидации файла: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 