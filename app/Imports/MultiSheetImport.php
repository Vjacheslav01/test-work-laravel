<?php

namespace App\Imports;

use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MultiSheetImport implements WithMultipleSheets
{
    private array $errors = [];
    private array $stats = [
        'categories' => ['total_rows' => 0, 'success_count' => 0, 'error_count' => 0, 'errors' => []],
        'products' => ['total_rows' => 0, 'success_count' => 0, 'error_count' => 0, 'errors' => []],
        'sales' => ['total_rows' => 0, 'success_count' => 0, 'error_count' => 0, 'errors' => []],
    ];

    // Сохраняем ссылки на импорт объекты
    private array $importInstances = [];

    /**
     * @return array
     */
    public function sheets(): array
    {
        // Создаем экземпляры и сохраняем ссылки
        $this->importInstances['Categories'] = new CategoriesImport();
        $this->importInstances['Products'] = new ProductsImport();
        $this->importInstances['Sales'] = new SalesImport();

        return [
            'Categories' => $this->importInstances['Categories'],
            'Products' => $this->importInstances['Products'],
            'Sales' => $this->importInstances['Sales'],
        ];
    }

    /**
     * Получить общую статистику импорта всех листов
     */
    public function getImportStats(): array
    {
        // Собираем статистику с сохраненных экземпляров
        foreach ($this->importInstances as $sheetName => $import) {
            $sheetStats = $import->getImportStats();
            $this->stats[strtolower($sheetName)] = $sheetStats;
        }

        // Подсчитываем общую статистику
        $totalStats = [
            'total_rows' => 0,
            'success_count' => 0,
            'error_count' => 0,
            'errors' => []
        ];

        foreach ($this->stats as $sheetName => $sheetStats) {
            $totalStats['total_rows'] += $sheetStats['total_rows'];
            $totalStats['success_count'] += $sheetStats['success_count'];
            $totalStats['error_count'] += $sheetStats['error_count'];
            
            // Добавляем ошибки с указанием листа
            foreach ($sheetStats['errors'] as $error) {
                $error['sheet'] = $sheetName;
                $totalStats['errors'][] = $error;
            }
        }

        return [
            'total' => $totalStats,
            'sheets' => $this->stats
        ];
    }

    /**
     * Получить ожидаемую структуру файла
     */
    public static function getExpectedStructure(): array
    {
        return [
            'Categories' => CategoriesImport::getExpectedHeaders(),
            'Products' => ProductsImport::getExpectedHeaders(),
            'Sales' => SalesImport::getExpectedHeaders(),
        ];
    }

    /**
     * Валидация структуры файла
     */
    public function validateFileStructure(string $filePath): array
    {
        try {
            $reader = \Maatwebsite\Excel\Facades\Excel::toArray($this, $filePath);
            
            $expectedSheets = ['Categories', 'Products', 'Sales'];
            $actualSheets = array_keys($reader);
            
            $missingSheets = array_diff($expectedSheets, $actualSheets);
            $extraSheets = array_diff($actualSheets, $expectedSheets);
            
            $errors = [];
            
            if (!empty($missingSheets)) {
                $errors[] = 'Отсутствуют обязательные листы: ' . implode(', ', $missingSheets);
            }
            
            if (!empty($extraSheets)) {
                $errors[] = 'Найдены дополнительные листы: ' . implode(', ', $extraSheets);
            }
            
            // Валидация заголовков каждого листа
            $expectedStructure = self::getExpectedStructure();
            
            foreach ($expectedSheets as $sheetName) {
                if (!isset($reader[$sheetName]) || empty($reader[$sheetName])) {
                    $errors[] = "Лист '{$sheetName}' пустой или не найден";
                    continue;
                }
                
                $headers = array_keys($reader[$sheetName][0] ?? []);
                $expectedHeaders = array_keys($expectedStructure[$sheetName]);
                $missingHeaders = array_diff($expectedHeaders, $headers);
                
                if (!empty($missingHeaders)) {
                    $errors[] = "На листе '{$sheetName}' отсутствуют колонки: " . implode(', ', $missingHeaders);
                }
            }
            
            if (!empty($errors)) {
                return [
                    'valid' => false,
                    'message' => 'Ошибки в структуре файла',
                    'errors' => $errors,
                    'expected_structure' => $expectedStructure
                ];
            }
            
            return [
                'valid' => true,
                'message' => 'Структура файла корректна',
                'structure' => $expectedStructure
            ];
            
        } catch (\Exception $e) {
            Log::error('File structure validation error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'valid' => false,
                'message' => 'Ошибка при валидации файла: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ];
        }
    }
} 