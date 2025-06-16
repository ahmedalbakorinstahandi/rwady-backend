<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'price' => $this->price,
            'sale_price' => $this->sale_price,
            'sku' => $this->sku,
            'stock' => $this->stock,
            'is_active' => $this->is_active,
            'featured' => $this->featured,
            'image' => $this->image,
            'gallery' => $this->gallery,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'meta_keywords' => $this->meta_keywords,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'brand' => new BrandResource($this->whenLoaded('brand')),
            'colors' => ProductColorResource::collection($this->whenLoaded('colors')),
            'cart_items' => CartItemResource::collection($this->whenLoaded('cartItems')),
            'order_products' => OrderProductResource::collection($this->whenLoaded('orderProducts')),
            'favorites' => UserFavoriteResource::collection($this->whenLoaded('favorites')),
            'related_products' => ProductResource::collection($this->whenLoaded('relatedProducts')),
            'seo' => new SeoResource($this->whenLoaded('seo')),
            'media' => MediaResource::collection($this->whenLoaded('media')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
} 