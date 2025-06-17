<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {

        $relatedProducts = $this->whenLoaded('relatedProducts', function() {
            return $this->relatedProducts;
        }, collect([]));
        
        $relatedCategoryProducts = $this->whenLoaded('relatedCategoryProducts', function() {
            return $this->relatedCategoryProducts; 
        }, collect([]));

        $relatedProducts = $relatedProducts instanceof \Illuminate\Http\Resources\MissingValue 
            ? collect([])
            : $relatedProducts->merge($relatedCategoryProducts instanceof \Illuminate\Http\Resources\MissingValue ? collect([]) : $relatedCategoryProducts);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'sku' => $this->sku,
            'price' => $this->price,
            'price_after_discount' => $this->price_after_discount,
            'price_discount_start' => $this->price_discount_start,
            'price_discount_end' => $this->price_discount_end,
            'cost_price' => $this->cost_price,
            'cost_price_after_discount' => $this->cost_price_after_discount,
            'cost_price_discount_start' => $this->cost_price_discount_start,
            'cost_price_discount_end' => $this->cost_price_discount_end,
            'availability' => $this->availability,
            'stock' => $this->stock,
            'stock_unlimited' => $this->stock_unlimited,
            'out_of_stock' => $this->out_of_stock,
            'minimum_purchase' => $this->minimum_purchase,
            'maximum_purchase' => $this->maximum_purchase,
            'requires_shipping' => $this->requires_shipping,
            'weight' => $this->weight,
            'length' => $this->length,
            'width' => $this->width,
            'height' => $this->height,
            'shipping_type' => $this->shipping_type,
            'shipping_rate_single' => $this->shipping_rate_single,
            'shipping_rate_multi' => $this->shipping_rate_multi,
            'is_recommended' => $this->is_recommended,
            'ribbon_text' => $this->ribbon_text,
            'ribbon_color' => $this->ribbon_color,
            'related_category_id' => $this->related_category_id,
            'related_category_limit' => $this->related_category_limit,
            'related_category' => new CategoryResource($this->whenLoaded('relatedCategory')),
            'related_category_products' => ProductResource::collection($relatedCategoryProducts),
            'categories' => CategoryResource::collection($this->whenLoaded('categories')),
            'brands' => BrandResource::collection($this->whenLoaded('brands')),
            'colors' => ProductColorResource::collection($this->whenLoaded('colors')),
            'cart_items' => CartItemResource::collection($this->whenLoaded('cartItems')),
            'order_products' => OrderProductResource::collection($this->whenLoaded('orderProducts')),
            'favorites' => UserFavoriteResource::collection($this->whenLoaded('favorites')),
            'related_products' => ProductResource::collection($relatedProducts),
            'media' => MediaResource::collection($this->whenLoaded('media')),
            'seo' => new SeoResource($this->whenLoaded('seo')),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
