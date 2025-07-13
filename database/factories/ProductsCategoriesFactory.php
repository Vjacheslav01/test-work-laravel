<?php

namespace Database\Factories;

use App\Models\ProductsCategories;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductsCategories>
 */
class ProductsCategoriesFactory extends Factory
{
    protected $model = ProductsCategories::class;

    private static $categories = [
        'Электроника',
        'Одежда',
        'Дом и быт',
        'Спорт',
        'Книги',
        'Красота',
        'Автотовары',
        'Игрушки',
        'Здоровье',
        'Сад и огород',
        'Канцтовары',
        'Музыка',
        'Фильмы',
        'Строительство',
        'Путешествия'
    ];

    private static $index = 0;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->randomElement(self::$categories),
        ];
    }

    /**
     * Состояние для конкретной категории
     */
    public function withName(string $name): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $name,
        ]);
    }

    /**
     * Сбросить уникальность для повторного использования
     */
    public function resetUnique(): static
    {
        $this->faker->unique($reset = true);
        return $this;
    }
}
