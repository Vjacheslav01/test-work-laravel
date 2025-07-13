<?php

namespace Database\Factories;

use App\Models\ProductsSales;
use App\Models\Products;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductsSales>
 */
class ProductsSalesFactory extends Factory
{
    protected $model = ProductsSales::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $saleDate = $this->faker->dateTimeBetween('-3 months', 'now');
        
        return [
            'product_id' => Products::factory(),
            'quantity' => $this->faker->randomElement([
                1, 1, 1, 1, 1, 1, 1, 1, // 40% одна штука
                2, 2, 2, 2, 2, 2, // 25% две штуки
                3, 3, 3, // 15% три штуки
                4, 4, // 10% четыре штуки
                5, // 5% пять штук
                6, 7, 8, 9, 10 // остальные редко
            ]),
            'price' => $this->faker->randomFloat(2, 500, 150000),
            'created_at' => $saleDate,
            'updated_at' => $saleDate,
        ];
    }

    /**
     * Продажи за последний месяц
     */
    public function lastMonth(): static
    {
        return $this->state(function (array $attributes) {
            $saleDate = $this->faker->dateTimeBetween('-1 month', 'now');
            
            return [
                'created_at' => $saleDate,
                'updated_at' => $saleDate,
            ];
        });
    }

    /**
     * Продажи за последнюю неделю
     */
    public function lastWeek(): static
    {
        return $this->state(function (array $attributes) {
            $saleDate = $this->faker->dateTimeBetween('-1 week', 'now');
            
            return [
                'created_at' => $saleDate,
                'updated_at' => $saleDate,
            ];
        });
    }

    /**
     * Большие продажи (больше 5 штук)
     */
    public function bulk(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity' => $this->faker->numberBetween(6, 20),
        ]);
    }

    /**
     * Продажи для конкретного продукта
     */
    public function forProduct(Products $product): static
    {
        return $this->state(fn (array $attributes) => [
            'product_id' => $product->id,
            'price' => $this->faker->randomFloat(2, 
                $product->price * 0.8, 
                $product->price * 1.2
            ),
        ]);
    }

    /**
     * Продажи с конкретной ценой
     */
    public function withPrice(float $price): static
    {
        return $this->state(fn (array $attributes) => [
            'price' => $price,
        ]);
    }
}
