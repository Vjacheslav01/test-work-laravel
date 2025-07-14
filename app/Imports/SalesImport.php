<?php

namespace App\Imports;

use App\Models\Products;
use App\Models\ProductsSales;
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

class SalesImport implements 
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
                    'sale_id' => 'required|integer|min:1',
                    'product_id' => 'required|integer|min:1',
                    'quantity' => 'required|integer|min:1',
                    'sale_date' => 'required|date',
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
                
                Log::error('Sales import error', [
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
        // Проверяем что продукт существует
        $product = Products::find((int) $data['product_id']);
        if (!$product) {
            throw new \Exception("Продукт с ID {$data['product_id']} не найден");
        }

        // Создать или обновить продажу с указанным ID
        ProductsSales::updateOrCreate(
            ['id' => (int) $data['sale_id']],
            [
                'product_id' => (int) $data['product_id'],
                'quantity' => (int) $data['quantity'],
                'price' => $product->price, // Используем цену из продукта
                'created_at' => \Carbon\Carbon::parse($data['sale_date']),
                'updated_at' => now(),
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

        Log::error('Sales import critical error', [
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
            'sale_id' => 'ID продажи',
            'product_id' => 'ID продукта',
            'quantity' => 'Количество',
            'sale_date' => 'Дата продажи'
        ];
    }
}
