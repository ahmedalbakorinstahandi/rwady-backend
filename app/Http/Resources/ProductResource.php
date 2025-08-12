<?php

namespace App\Http\Resources;

use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {



        $user = User::auth();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'sku' => $this->sku,
            'price' => $this->price,
            'price_after_discount' => $this->price_after_discount,
            'price_discount_start' => $this->price_discount_start,
            'price_discount_end' => $this->price_discount_end,
            'cost_price' => $user && $user->isAdmin() ? $this->cost_price : 0.0,
            'cost_price_after_discount' => $user && $user->isAdmin() ? $this->cost_price_after_discount : 0.0,
            'cost_price_discount_start' => $user && $user->isAdmin() ? $this->cost_price_discount_start : 0.0,
            'cost_price_discount_end' => $user && $user->isAdmin() ? $this->cost_price_discount_end : 0.0,
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
            'is_favorite' => $user ? $user->favorites()->where('product_id', $this->id)->exists() : false,
            'discount_percentage_text' => $this->discount_percentage,
            'sort_orders' => $this->orders,
            'total_orders' => $this->total_orders,
            // 'promotion' => $this->getBestPromotionAttribute(),
            'final_price_after_promotion' => $this->final_price_after_promotion,
            'promotion' => new PromotionResource($this->getBestPromotionAttribute()),
            'related_category' => new CategoryResource($this->whenLoaded('relatedCategory')),
            'related_products' => $this->whenLoaded('relatedProducts', function () {
                try {
                    // First: Get manually related products
                    $manualRelatedProducts = collect($this->relatedProducts ?? []);
                    
                    // Second: Get products from same lowest level categories
                    $sameLevelProducts = collect($this->sameLevelProducts ?? []);
                    
                    // Merge both collections
                    $merged = $manualRelatedProducts->merge($sameLevelProducts);
                    
                    // Remove duplicates and apply limit
                    $limit = $this->related_category_limit ?: 10;
                    $merged = $merged->unique('id')->take($limit);
                    
                    return ProductResource::collection($merged);
                } catch (\Exception $e) {
                    return ProductResource::collection(collect([]));
                }
            }, []),
            'categories' => CategoryResource::collection($this->whenLoaded('categories')),
            'brands' => BrandResource::collection($this->whenLoaded('brands')),
            'colors' => ProductColorResource::collection($this->whenLoaded('colors')),
            'cart_items' => CartItemResource::collection($this->whenLoaded('cartItems')),
            'order_products' => OrderProductResource::collection($this->whenLoaded('orderProducts')),
            'media' => MediaResource::collection($this->whenLoaded('media')),
            'seo' => new SeoResource($this->whenLoaded('seo')),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
