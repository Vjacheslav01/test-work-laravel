<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SaleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'product_name' => $this->product->name,
            'category' => $this->product->category->name,
            'quantity' => $this->quantity,
            'price' => $this->price,
            'total_amount' => $this->price * $this->quantity,
            'sale_date' => $this->created_at->format('Y-m-d'),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
            
            // Включаем связанные данные только при необходимости
            'product' => $this->whenLoaded('product', function () {
                return [
                    'id' => $this->product->id,
                    'name' => $this->product->name,
                    'description' => $this->product->description,
                    'price' => $this->product->price,
                    'category' => $this->whenLoaded('product.category', function () {
                        return [
                            'id' => $this->product->category->id,
                            'name' => $this->product->category->name,
                        ];
                    }),
                ];
            }),
        ];
    }
}
