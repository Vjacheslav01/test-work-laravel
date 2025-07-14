<?php

namespace App\Imports;

use App\Models\Products;
use App\Models\ProductsCategories;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Validators\Failure;
use Throwable;

class ProductsImport implements 
    ToCollection, 
    WithHeadingRow, 
    WithChunkReading,
    SkipsOnError,
    SkipsOnFailure
{
    private array $errors = [];
    private int $successCount = 0;
    private int $totalRows = 0;

    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection): void
    {
        foreach ($collection as $index => $row) {
            $this->totalRows++;
            
            try {
                // Валидация строки
                $validator = Validator::make($row->toArray(), [
                    'product_id' => 'required|integer|min:1',
                    'product_name' => 'required|string|max:255',
                    'category_id' => 'required|integer|min:1',
                    'price' => 'required|numeric|min:0',
                ]);

                if ($validator->fails()) {
                    $this->errors[] = [
                        'row' => $index + 2,
                        'errors' => $validator->errors()->toArray(),
                        'data' => $row->toArray()
                    ];
                    continue;
                }

                $this->processRow($row->toArray(), $index + 2);
                
            } catch (Throwable $e) {
                $this->errors[] = [
                    'row' => $index + 2,
                    'errors' => ['general' => [$e->getMessage()]],
                    'data' => $row->toArray()
                ];
                
                Log::error('Products import error', [
                    'row' => $index + 2,
                    'error' => $e->getMessage(),
                    'data' => $row->toArray()
                ]);
            }
        }
    }

    /**
     * Обработка одной строки
     */
    private function processRow(array $data, int $rowNumber): void
    {
        // Проверяем что категория существует
        $category = ProductsCategories::find((int) $data['category_id']);
        if (!$category) {
            throw new \Exception("Категория с ID {$data['category_id']} не найдена");
        }

        // Создать или обновить продукт с указанным ID
        Products::updateOrCreate(
            ['id' => (int) $data['product_id']],
            [
                'name' => trim($data['product_name']),
                'description' => 'Импортировано из Excel',
                'price' => (float) $data['price'],
                'category_id' => (int) $data['category_id']
            ]
        );

        $this->successCount++;
    }

    /**
     * @param Throwable $e
     */
    public function onError(Throwable $e): void
    {
        $this->errors[] = [
            'row' => 'unknown',
            'errors' => ['general' => [$e->getMessage()]],
            'data' => []
        ];

        Log::error('Products import critical error', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }

    /**
     * @param Failure[] $failures
     */
    public function onFailure(Failure ...$failures): void
    {
        foreach ($failures as $failure) {
            $this->errors[] = [
                'row' => $failure->row(),
                'errors' => $failure->errors(),
                'data' => $failure->values()
            ];
        }
    }

    /**
     * @return int
     */
    public function chunkSize(): int
    {
        return 100;
    }

    /**
     * Получить статистику импорта
     */
    public function getImportStats(): array
    {
        return [
            'total_rows' => $this->totalRows,
            'success_count' => $this->successCount,
            'error_count' => count($this->errors),
            'errors' => $this->errors
        ];
    }

    /**
     * Получить заголовки, которые ожидаются в Excel файле
     */
    public static function getExpectedHeaders(): array
    {
        return [
            'product_id' => 'ID продукта',
            'product_name' => 'Название продукта',
            'category_id' => 'ID категории',
            'price' => 'Цена'
        ];
    }
} 