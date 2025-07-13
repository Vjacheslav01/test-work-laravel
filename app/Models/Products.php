<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ProductsSales;
use App\Models\ProductsCategories;

/**
 * @property int $id
 * @property string $name
 * @property string $description
 * @property float $price
 * @property int $category_id
 */
class Products extends Model
{
    use HasFactory;

    /**
     * Свойства, которые можно заполнять массово
     *
     * @var list<string>
     */
    protected $fillable = ['name', 'description', 'price', 'category_id'];

    /**
     * Связь с таблицей продаж.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function sales()
    {
        return $this->hasMany(ProductsSales::class);
    }

    /**
     * Связь с таблицей категорий.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(ProductsCategories::class);
    }

    /**
     * Получить отчет о продажах для продукта.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function getSalesReport()
    {
        return $this->sales()->get();
    }
}