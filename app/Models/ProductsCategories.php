<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Products;

/**
 * @property int $id
 * @property string $name
 */
class ProductsCategories extends Model
{
    use HasFactory;

    /**
     * Свойства, которые можно заполнять массово
     *
     * @var list<string>
     */
    protected $fillable = ['name'];

    /**
     * Связь с таблицей продуктов.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function products()
    {
        return $this->hasMany(Products::class, 'category_id');
    }
}