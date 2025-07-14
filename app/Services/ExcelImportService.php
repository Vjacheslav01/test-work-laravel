<?php

namespace App\Services;

use App\Imports\MultiSheetImport;
use App\Imports\CategoriesImport;
use App\Imports\ProductsImport;
use App\Imports\SalesImport;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ExcelImportService
{
    /**
     * Обработка импорта файла
     */
    public function importSalesFile(UploadedFile $file): array
    {
        try {
            // Сохраняем файл временно
            $filePath = $this->storeTemporaryFile($file);
            
            // Создаем объект импорта
            $import = new MultiSheetImport();
            
            // Сначала валидируем структуру файла
            $validation = $import->validateFileStructure($filePath);
            if (!$validation['valid']) {
                $this->cleanupTemporaryFile($filePath);
                return [
                    'success' => false,
                    'message' => $validation['message'],
                    'errors' => $validation['errors'] ?? [$validation['message']],
                    'validation_errors' => true
                ];
            }
            
            // Выполняем импорт
            Excel::import($import, $filePath);
            
            // Получаем статистику
            $stats = $import->getImportStats();
            
            // Удаляем временный файл
            $this->cleanupTemporaryFile($filePath);
            
            // Логируем результат
            Log::info('Multi-sheet Excel import completed', [
                'file_name' => $file->getClientOriginalName(),
                'stats' => $stats
            ]);
            
            return [
                'success' => true,
                'message' => $this->generateSuccessMessage($stats['total']),
                'stats' => $stats
            ];
            
        } catch (\Exception $e) {
            Log::error('Multi-sheet Excel import failed', [
                'file_name' => $file->getClientOriginalName(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'message' => 'Ошибка при импорте файла: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Валидация структуры файла
     */
    public function validateFileStructure(UploadedFile $file): array
    {
        try {
            // Проверяем структуру файла
            $filePath = $this->storeTemporaryFile($file);
            
            $import = new MultiSheetImport();
            $validation = $import->validateFileStructure($filePath);
            
            $this->cleanupTemporaryFile($filePath);
            
            return $validation;
            
        } catch (\Exception $e) {
            return [
                'valid' => false,
                'message' => 'Ошибка при валидации файла: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Получение шаблона Excel файла
     */
    public function generateTemplate(): string
    {
        // Создаем временный файл с шаблоном
        $fileName = 'multi_sheet_template_' . now()->format('Y_m_d_H_i_s') . '.xlsx';
        
        // Убеждаемся что папка temp существует
        Storage::disk('local')->makeDirectory('temp');

        // Создаем Excel файл с тремя листами используя явные названия
        Excel::store(
            new class implements \Maatwebsite\Excel\Concerns\WithMultipleSheets, \Maatwebsite\Excel\Concerns\WithTitle {
                public function sheets(): array
                {
                    return [
                        0 => new class implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithHeadings, \Maatwebsite\Excel\Concerns\WithTitle {
                            public function array(): array
                            {
                                return [
                                    [1, 'Электроника'],
                                    [2, 'Одежда'],
                                    [3, 'Книги'],
                                    [4, 'Спорт и отдых'],
                                    [5, 'Дом и сад'],
                                    [6, 'Красота и здоровье'],
                                    [7, 'Автотовары'],
                                    [8, 'Игрушки'],
                                    [9, 'Продукты питания'],
                                    [10, 'Бытовая техника'],
                                ];
                            }

                            public function headings(): array
                            {
                                return ['category_id', 'category_name'];
                            }

                            public function title(): string
                            {
                                return 'Categories';
                            }
                        },
                        1 => new class implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithHeadings, \Maatwebsite\Excel\Concerns\WithTitle {
                            public function array(): array
                            {
                                return [
                                    [1, 'iPhone 14', 1, 65000],
                                    [2, 'Samsung Galaxy S23', 1, 55000],
                                    [3, 'Nike Air Max', 2, 12000],
                                    [4, 'Adidas Superstar', 2, 12000],
                                    [5, 'Laptops Samsung', 1, 12000],
                                    [6, 'Laptops Apple', 1, 12000],
                                    [7, 'Laptops Lenovo', 1, 12000],
                                    [8, 'Laptops Asus', 1, 12000],
                                    [9, 'iPad Pro', 1, 85000],
                                    [10, 'MacBook Air', 1, 120000],
                                    [11, 'Фэнтези роман', 3, 500],
                                    [12, 'Программирование на PHP', 3, 1200],
                                    [13, 'Футбольный мяч', 4, 2500],
                                    [14, 'Теннисная ракетка', 4, 8000],
                                    [15, 'Садовые ножницы', 5, 1500],
                                    [16, 'Лопата', 5, 800],
                                    [17, 'Крем для лица', 6, 2200],
                                    [18, 'Шампунь', 6, 650],
                                    [19, 'Автомобильный коврик', 7, 3500],
                                    [20, 'Моторное масло', 7, 1800],
                                    [21, 'Конструктор LEGO', 8, 4500],
                                    [22, 'Кукла Барби', 8, 2800],
                                    [23, 'Шоколад молочный', 9, 250],
                                    [24, 'Кофе зерновой', 9, 850],
                                    [25, 'Пылесос', 10, 15000],
                                    [26, 'Микроволновка', 10, 8500],
                                ];
                            }

                            public function headings(): array
                            {
                                return ['product_id', 'product_name', 'category_id', 'price'];
                            }

                            public function title(): string
                            {
                                return 'Products';
                            }
                        },
                        2 => new class implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithHeadings, \Maatwebsite\Excel\Concerns\WithTitle {
                            public function array(): array
                            {
                                return [
                                    [1, 1, 2, '2025-06-15'],
                                    [2, 2, 1, '2025-04-16'],
                                    [3, 3, 1, '2025-06-17'],
                                    [4, 4, 1, '2025-04-18'],
                                    [5, 5, 1, '2025-05-19'],
                                    [6, 6, 1, '2025-03-20'],
                                    [7, 7, 1, '2025-01-21'],
                                    [8, 8, 1, '2025-02-22'],
                                    [9, 9, 1, '2025-06-23'],
                                    [10, 10, 2, '2025-02-24'],
                                    [11, 11, 3, '2025-02-25'],
                                    [12, 12, 1, '2025-06-26'],
                                    [13, 13, 2, '2025-05-27'],
                                    [14, 14, 1, '2025-03-28'],
                                    [15, 15, 4, '2025-02-28'],
                                    [16, 16, 2, '2025-06-30'],
                                    [17, 17, 1, '2025-07-01'],
                                    [18, 18, 3, '2025-05-02'],
                                    [19, 19, 1, '2025-07-03'],
                                    [20, 20, 2, '2025-03-04'],
                                    [21, 21, 1, '2025-06-05'],
                                    [22, 22, 2, '2025-02-06'],
                                    [23, 23, 5, '2025-06-07'],
                                    [24, 24, 3, '2025-04-08'],
                                    [25, 25, 1, '2025-05-09'],
                                    [26, 26, 2, '2025-02-10'],
                                    [27, 1, 1, '2025-02-11'],
                                    [28, 2, 2, '2025-02-12'],
                                    [29, 9, 3, '2025-02-13'],
                                    [30, 25, 1, '2025-02-14'],
                                ];
                            }

                            public function headings(): array
                            {
                                return ['sale_id', 'product_id', 'quantity', 'sale_date'];
                            }

                            public function title(): string
                            {
                                return 'Sales';
                            }
                        },
                    ];
                }

                public function title(): string
                {
                    return 'Template';
                }
            },
            'temp/' . $fileName,
            'local'
        );

        return Storage::disk('local')->path('temp/' . $fileName);
    }

    /**
     * Сохранение временного файла
     */
    private function storeTemporaryFile(UploadedFile $file): string
    {
        $fileName = 'import_' . uniqid() . '.' . $file->getClientOriginalExtension();
        
        // Убеждаемся что папка temp существует
        Storage::disk('local')->makeDirectory('temp');
        
        $filePath = $file->storeAs('temp', $fileName, 'local');
        
        return Storage::disk('local')->path($filePath);
    }

    /**
     * Удаление временного файла
     */
    private function cleanupTemporaryFile(string $filePath): void
    {
        // Получаем относительный путь от storage/app/
        $relativePath = str_replace(storage_path('app/'), '', $filePath);
        
        if (Storage::disk('local')->exists($relativePath)) {
            Storage::disk('local')->delete($relativePath);
        }
    }

    /**
     * Генерация сообщения об успешном импорте
     */
    private function generateSuccessMessage(array $stats): string
    {
        $message = "Импорт завершен. ";
        $message .= "Успешно обработано: {$stats['success_count']} строк";
        
        if ($stats['error_count'] > 0) {
            $message .= ", ошибок: {$stats['error_count']}";
        }
        
        $message .= " из {$stats['total_rows']} общих строк.";
        
        return $message;
    }
} 