<?php

namespace App\Services;

use App\Models\ProductsSales;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class SalesService
{
    /**
     * Получение отфильтрованных продаж с пагинацией
     */
    public function getSalesWithFilters(Request $request): LengthAwarePaginator
    {
        $query = $this->buildSalesQuery($request);
        
        return $query->paginate(
            $request->get('per_page', 10),
            ['*'],
            'page',
            $request->get('page', 1)
        );
    }

    /**
     * Получение данных для графика
     */
    public function getChartData(Request $request): array
    {
        $query = $this->buildSalesQuery($request, forChart: true);
        
        $sales = $query->get();

        return $this->formatChartData($sales);
    }

    /**
     * Построение базового запроса с фильтрами
     */
    private function buildSalesQuery(Request $request, bool $forChart = false): Builder
    {
        $query = ProductsSales::with(['product.category'])
            ->orderBy('created_at', $forChart ? 'asc' : 'desc');

        // Фильтр по дате от
        if ($request->filled('dateFrom')) {
            $query->where('created_at', '>=', $request->dateFrom . ' 00:00:00');
        }

        // Фильтр по дате до  
        if ($request->filled('dateTo')) {
            $query->where('created_at', '<=', $request->dateTo . ' 23:59:59');
        }

        // Фильтр по категории
        if ($request->filled('category')) {
            $query->whereHas('product.category', function ($q) use ($request) {
                $q->where('name', $request->category);
            });
        }

        return $query;
    }

    /**
     * Форматирование данных для графика
     */
    private function formatChartData($sales): array
    {
        $salesByDate = [];
        
        foreach ($sales as $sale) {
            $date = $sale->created_at->format('Y-m-d');
            
            if (!isset($salesByDate[$date])) {
                $salesByDate[$date] = 0;
            }
            
            $salesByDate[$date] += $sale->price * $sale->quantity;
        }

        // Сортируем по датам
        ksort($salesByDate);

        return [
            'labels' => array_map(function($date) {
                return \Carbon\Carbon::parse($date)->format('d.m.Y');
            }, array_keys($salesByDate)),
            'data' => array_values($salesByDate)
        ];
    }

    /**
     * Получение списка доступных категорий
     */
    public function getAvailableCategories(): array
    {
        return ProductsSales::with('product.category')
            ->get()
            ->pluck('product.category.name')
            ->unique()
            ->filter()
            ->values()
            ->toArray();
    }
} 