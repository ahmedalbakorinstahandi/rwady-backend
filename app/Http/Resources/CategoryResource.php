<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Cache;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'parent_id' => $this->parent_id,
            'image' => $this->image,
            'image_url' => $this->image ? url('storage/' . $this->image) : null,
            'availability' => $this->availability,
            'orders' => $this->orders,
            'products_count' => Cache::remember("category_{$this->id}_products_count", 3600*24, function () {
                return $this->products->count();
            }),
            'parent' => new CategoryResource($this->whenLoaded('parent')),
            'children' => CategoryResource::collection($this->whenLoaded('children')),
            'promotion' => new PromotionResource($this->getBestPromotionAttribute()),
            // 'products' => ProductResource::collection($this->whenLoaded('products')),
            'seo' => new SeoResource($this->whenLoaded('seo')),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
