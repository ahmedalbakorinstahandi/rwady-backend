<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'product_id' => $this->product_id,
            'quantity' => $this->quantity,
            'price' => $this->price,
            'total' => $this->total,
            'color_id' => $this->color_id,
            'promotion_id' => $this->promotion_id,
            'promotion_title' => $this->promotion_title, 
            'promotion_discount_type' => $this->promotion_discount_type,
            'promotion_discount_value' => $this->promotion_discount_value,
            'order' => new OrderResource($this->whenLoaded('order')),
            'product' => new ProductResource($this->whenLoaded('product')),
            'color' => new ProductColorResource($this->whenLoaded('color')),
            'promotion' => new PromotionResource($this->whenLoaded('promotion')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
} 