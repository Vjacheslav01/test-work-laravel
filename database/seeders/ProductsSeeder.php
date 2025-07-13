<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ProductsCategories;
use App\Models\Products;
use App\Models\ProductsSales;

class ProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Начинаем заполнение данными...');

        // Создаем основные категории
        $categories = [
            'Электроника' => 'electronics',
            'Одежда' => 'clothing',
            'Дом и быт' => 'home',
            'Спорт' => 'sport',
        ];

        $createdCategories = [];
        
        foreach ($categories as $categoryName => $factoryState) {
            $category = ProductsCategories::factory()->withName($categoryName)->create();
            $createdCategories[$factoryState] = $category;
            $this->command->info("✅ Создана категория: {$categoryName}");
        }

        // Создаем продукты для каждой категории
        $products = [];
        
        // Электроника - 15 продуктов
        $electronicsProducts = Products::factory()
            ->count(15)
            ->electronics()
            ->forCategory($createdCategories['electronics'])
            ->create();
        $products = array_merge($products, $electronicsProducts->toArray());

        // Одежда - 12 продуктов
        $clothingProducts = Products::factory()
            ->count(12)
            ->clothing()
            ->forCategory($createdCategories['clothing'])
            ->create();
        $products = array_merge($products, $clothingProducts->toArray());

        // Дом и быт - 10 продуктов
        $homeProducts = Products::factory()
            ->count(10)
            ->home()
            ->forCategory($createdCategories['home'])
            ->create();
        $products = array_merge($products, $homeProducts->toArray());

        // Спорт - 8 продуктов
        $sportProducts = Products::factory()
            ->count(8)
            ->sport()
            ->forCategory($createdCategories['sport'])
            ->create();
        $products = array_merge($products, $sportProducts->toArray());

        $this->command->info('✅ Создано продуктов: ' . count($products));

        // Создаем продажи для каждого продукта
        $totalSales = 0;
        
        foreach ($products as $product) {
            $productModel = Products::find($product['id']);
            
            // Каждый продукт имеет от 1 до 5 продаж
            $salesCount = fake()->numberBetween(1, 5);
            
            // Создаем продажи за разные периоды
            for ($i = 0; $i < $salesCount; $i++) {
                $saleType = fake()->randomElement(['lastMonth', 'lastWeek', 'regular']);
                
                $sale = ProductsSales::factory()
                    ->forProduct($productModel);
                
                if ($saleType === 'lastMonth') {
                    $sale = $sale->lastMonth();
                } elseif ($saleType === 'lastWeek') {
                    $sale = $sale->lastWeek();
                }
                
                // Иногда создаем большие продажи
                if (fake()->boolean(15)) { // 15% вероятность
                    $sale = $sale->bulk();
                }
                
                $sale->create();
                $totalSales++;
            }
        }

        // Создаем дополнительные случайные продажи
        ProductsSales::factory()
            ->count(30)
            ->state(function (array $attributes) use ($products) {
                $randomProduct = fake()->randomElement($products);
                return [
                    'product_id' => $randomProduct['id'],
                    'price' => fake()->randomFloat(2, 
                        $randomProduct['price'] * 0.8, 
                        $randomProduct['price'] * 1.2
                    ),
                ];
            })
            ->create();

        $totalSales += 30;

        // Выводим статистику
        $this->command->info('');
        $this->command->info('📊 === СТАТИСТИКА === 📊');
        $this->command->info('📁 Категорий создано: ' . ProductsCategories::count());
        $this->command->info('📦 Продуктов создано: ' . Products::count());
        $this->command->info('💰 Продаж создано: ' . ProductsSales::count());
        $this->command->info('');
        
        // Статистика по категориям
        foreach ($createdCategories as $key => $category) {
            $productCount = $category->products()->count();
            $salesCount = ProductsSales::whereHas('product', function ($query) use ($category) {
                $query->where('category_id', $category->id);
            })->count();
            
            $this->command->info("📊 {$category->name}: {$productCount} продуктов, {$salesCount} продаж");
        }
        
        $this->command->info('');
        $this->command->info('🎉 Заполнение завершено успешно!');
    }
}
