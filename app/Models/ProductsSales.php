<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Products;

/**
 * @property int $id
 * @property int $product_id
 * @property int $quantity
 * @property float $price
 * @property Products $product
 */
class ProductsSales extends Model
{
    use HasFactory;

    /**
     * Свойства, которые можно заполнять массово
     *
     * @var list<string>
     */
    protected $fillable = ['product_id', 'quantity', 'price', 'created_at'];

    /**
     * Приведение типов
     *
     * @var array
     */
    protected $casts = [
        'quantity' => 'integer',
        'price' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Связь с таблицей продуктов.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(Products::class);
    }
}