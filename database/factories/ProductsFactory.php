<?php

namespace Database\Factories;

use App\Models\Products;
use App\Models\ProductsCategories;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Products>
 */
class ProductsFactory extends Factory
{
    protected $model = Products::class;

    private static $electronics = [
        'iPhone 15 Pro' => [50000, 120000],
        'Samsung Galaxy S24' => [45000, 90000],
        'MacBook Pro M3' => [150000, 300000],
        'Sony PlayStation 5' => [40000, 60000],
        'iPad Air' => [35000, 80000],
        'Apple Watch Series 9' => [25000, 50000],
        'Sony WH-1000XM5' => [15000, 30000],
        'Nintendo Switch' => [20000, 35000],
        'Xiaomi Mi Band 8' => [3000, 8000],
        'AirPods Pro' => [15000, 25000],
    ];

    private static $clothing = [
        'Nike Air Force 1' => [8000, 15000],
        'Adidas Ultraboost' => [10000, 20000],
        'Levi\'s 501 Original' => [4000, 8000],
        'Куртка зимняя' => [5000, 15000],
        'Кроссовки New Balance' => [7000, 12000],
        'Свитер шерстяной' => [3000, 8000],
        'Джинсы классические' => [2500, 6000],
        'Кроссовки Puma' => [6000, 12000],
        'Футболка хлопковая' => [1000, 3000],
        'Костюм деловой' => [15000, 40000],
    ];

    private static $home = [
        'Кофемашина Nespresso' => [8000, 25000],
        'Пылесос Dyson' => [15000, 35000],
        'Мультиварка Redmond' => [3000, 8000],
        'Утюг Philips' => [2000, 5000],
        'Блендер Vitamix' => [10000, 25000],
        'Постельное белье' => [2000, 8000],
        'Набор кастрюль' => [3000, 12000],
        'Микроволновка Samsung' => [5000, 15000],
        'Сковорода чугунная' => [1500, 4000],
        'Холодильник Bosch' => [40000, 120000],
    ];

    private static $sport = [
        'Кроссовки для бега' => [5000, 15000],
        'Гантели разборные' => [3000, 8000],
        'Мяч футбольный' => [1000, 3000],
        'Ракетка теннисная' => [2000, 8000],
        'Коврик для йоги' => [1000, 3000],
        'Велосипед горный' => [25000, 80000],
        'Лыжи беговые' => [8000, 20000],
        'Скакалка' => [500, 1500],
        'Гири' => [2000, 5000],
        'Эспандер' => [500, 2000],
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(10),
            'price' => $this->faker->randomFloat(2, 1000, 50000),
            'category_id' => ProductsCategories::factory(),
        ];
    }

    /**
     * Состояние для электроники
     */
    public function electronics(): static
    {
        return $this->state(function (array $attributes) {
            $product = $this->faker->randomElement(array_keys(self::$electronics));
            $priceRange = self::$electronics[$product];
            
            return [
                'name' => $product,
                'description' => $this->faker->sentence(8),
                'price' => $this->faker->randomFloat(2, $priceRange[0], $priceRange[1]),
            ];
        });
    }

    /**
     * Состояние для одежды
     */
    public function clothing(): static
    {
        return $this->state(function (array $attributes) {
            $product = $this->faker->randomElement(array_keys(self::$clothing));
            $priceRange = self::$clothing[$product];
            
            return [
                'name' => $product,
                'description' => $this->faker->sentence(8),
                'price' => $this->faker->randomFloat(2, $priceRange[0], $priceRange[1]),
            ];
        });
    }

    /**
     * Состояние для дома и быта
     */
    public function home(): static
    {
        return $this->state(function (array $attributes) {
            $product = $this->faker->randomElement(array_keys(self::$home));
            $priceRange = self::$home[$product];
            
            return [
                'name' => $product,
                'description' => $this->faker->sentence(8),
                'price' => $this->faker->randomFloat(2, $priceRange[0], $priceRange[1]),
            ];
        });
    }

    /**
     * Состояние для спорта
     */
    public function sport(): static
    {
        return $this->state(function (array $attributes) {
            $product = $this->faker->randomElement(array_keys(self::$sport));
            $priceRange = self::$sport[$product];
            
            return [
                'name' => $product,
                'description' => $this->faker->sentence(8),
                'price' => $this->faker->randomFloat(2, $priceRange[0], $priceRange[1]),
            ];
        });
    }

    /**
     * Создать продукт с существующей категорией
     */
    public function forCategory(ProductsCategories $category): static
    {
        return $this->state(fn (array $attributes) => [
            'category_id' => $category->id,
        ]);
    }
}
