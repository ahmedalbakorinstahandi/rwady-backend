<?php

namespace App\Http\Services;

use App\Http\Permissions\ProductPermission;
use App\Models\Product;
use App\Services\FilterService;
use App\Services\LanguageService;
use App\Services\MessageService;
use App\Services\OrderHelper;
use Illuminate\Support\Str;


class ProductService
{
    public function index(array $filters = [])
    {
        $query = Product::query()->with(['media', 'colors']);

        $searchFields = ['name', 'description', 'sku'];
        $numericFields = [
            'price',
            'price_after_discount',
            'cost_price',
            'cost_price_after_discount',
            'stock',
            'minimum_purchase',
            'maximum_purchase',
            'weight',
            'length',
            'width',
            'height',
            'shipping_rate_single',
            'shipping_rate_multi'
        ];
        $dateFields = [
            'price_discount_start',
            'price_discount_end',
            'cost_price_discount_start',
            'cost_price_discount_end',
            'created_at',
        ];
        $exactMatchFields = [
            'id',
            'view_in_home',
            'availability',
            'stock_unlimited',
            'out_of_stock',
            'shipping_type',
            'related_category_id'
        ];
        $inFields = [];

        if (isset($filters['category_id'])) {
            $query->whereHas('categories', function ($query) use ($filters) {
                $query->where('category_id', $filters['category_id']);
            });
        }

        if (isset($filters['brand_id'])) {
            $query->whereHas('brands', function ($query) use ($filters) {
                $query->where('brand_id', $filters['brand_id']);
            });
        }

        if (isset($filters['is_recommended'])) {
            $query->where('is_recommended', $filters['is_recommended']);
        }

        if (isset($filters['most_sold'])) {
            $query->withCount('orderProducts')->orderBy('order_products_count', 'desc');
        }

        $query = ProductPermission::filterIndex($query);

        $query = FilterService::applyFilters(
            $query,
            $filters,
            $searchFields,
            $numericFields,
            $dateFields,
            $exactMatchFields,
            $inFields
        );

        return $query;
    }

    public function show(int $id)
    {
        $product = Product::where('id', $id)->first();

        if (!$product) {
            MessageService::abort(404, 'messages.product.not_found');
        }

        $product->load(['relatedCategory', 'brands', 'colors', 'relatedProducts', 'categories', 'media', 'seo']);

        return $product;
    }

    public function create($data)
    {
        if (empty($data['sku'])) {
            $data['sku'] = Str::random(10);
        }

        if (empty($data['out_of_stock'])) {
            $data['out_of_stock'] = "show_on_storefront";
        }

        if (empty($data['stock_unlimited'])) {
            $data['stock_unlimited'] = false;
        }

        $data = LanguageService::prepareTranslatableData($data, new Product);

        $product = Product::create($data);

        $product->sku = $product->id;
        $product->save();

        // Handle media (images)
        if (isset($data['images'])) {
            $mediaData = array_map(function ($image) {
                return [
                    'path' => $image,
                    'type' => 'image',
                    'source' => 'file',
                    'orders' => 0,
                ];
            }, $data['images']);

            $media = $product->media()->createMany($mediaData);
            foreach ($media as $item) {
                OrderHelper::assign($item);
            }
        }

        // Handle media (videos)
        if (isset($data['videos'])) {
            $mediaData = array_map(function ($video) {
                return [
                    'path' => $video,
                    'type' => 'video',
                    'source' => 'link',
                    'orders' => 0,
                ];
            }, $data['videos']);

            $media = $product->media()->createMany($mediaData);
            foreach ($media as $item) {
                OrderHelper::assign($item);
            }
        }

        // Sync categories
        if (isset($data['categories'])) {
            $categoryData = array_fill_keys($data['categories'], []);
            $product->categories()->sync($categoryData);
        }

        // Sync brands
        if (isset($data['brands'])) {
            $brandData = array_fill_keys($data['brands'], []);
            $product->brands()->sync($brandData);
        }

        // Sync colors
        if (isset($data['colors'])) {
            $colorData = array_map(function ($color) {
                return ['color' => $color];
            }, $data['colors']);
            $product->colors()->createMany($colorData);
        }

        // Sync related products
        if (isset($data['related_products'])) {
            $product->relatedProducts()->sync($data['related_products']);
        }

        // Create or update SEO
        if (isset($data['seo'])) {
            $product->seo()->updateOrCreate(
                [
                    'seoable_type' => Product::class,
                    'seoable_id' => $product->id,
                ],
                [
                    'meta_title' => $data['seo']['meta_title'] ?? null,
                    'meta_description' => $data['seo']['meta_description'] ?? null,
                    'keywords' => $data['seo']['keywords'] ?? null,
                    'image' => $data['seo']['image'] ?? null,
                ]
            );
        }

        $product = $this->show($product->id);

        return $product;
    }

    public function update($data, $product)
    {
        $data = LanguageService::prepareTranslatableData($data, $product);


        $allow_attributes = [
            'related_category_id',
            'price_after_discount',
            'price_discount_start',
            'price_discount_end',
            'cost_price_after_discount',
            'cost_price_discount_start',
            'cost_price_discount_end',
        ];


        // unset not allow attributes if value is null
        foreach ($data as $key => $value) {
            if (!in_array($key, $allow_attributes) && is_null($value)) {
                unset($data[$key]);
            }
        }

        $product->update($data, $allow_attributes);

        // Handle media (images)
        if (isset($data['images'])) {
            $product->media()->where('type', 'image')->delete();
            $mediaData = array_map(function ($image) {
                return [
                    'path' => $image,
                    'type' => 'image',
                    'source' => 'file',
                    'orders' => 0,
                ];
            }, $data['images']);

            $media = $product->media()->createMany($mediaData);
            foreach ($media as $item) {
                OrderHelper::assign($item);
            }
        }

        // Handle media (videos)
        if (isset($data['videos'])) {
            $product->media()->where('type', 'video')->delete();
            $mediaData = array_map(function ($video) {
                return [
                    'path' => $video,
                    'type' => 'video',
                    'source' => 'link',
                    'orders' => 0,
                ];
            }, $data['videos']);

            $media = $product->media()->createMany($mediaData);
            foreach ($media as $item) {
                OrderHelper::assign($item);
            }
        }

        // Sync categories
        if (isset($data['categories'])) {
            $categoryData = array_fill_keys($data['categories'], []);
            $product->categories()->sync($categoryData);
        }

        // Sync brands
        if (isset($data['brands'])) {
            $brandData = array_fill_keys($data['brands'], []);
            $product->brands()->sync($brandData);
        }

        // Sync colors
        if (isset($data['colors'])) {
            $product->colors()->delete();
            $colorData = array_map(function ($color) {
                return ['color' => $color];
            }, $data['colors']);
            $product->colors()->createMany($colorData);
        }

        // Sync related products
        if (isset($data['related_products'])) {
            $product->relatedProducts()->sync($data['related_products']);
        }

        // Update SEO
        if (isset($data['seo'])) {
            $product->seo()->updateOrCreate(
                [
                    'seoable_type' => Product::class,
                    'seoable_id' => $product->id,
                ],
                [
                    'meta_title' => $data['seo']['meta_title'] ?? null,
                    'meta_description' => $data['seo']['meta_description'] ?? null,
                    'keywords' => $data['seo']['keywords'] ?? null,
                    'image' => $data['seo']['image'] ?? null,
                ]
            );
        }


        $product = $this->show($product->id);

        return $product;
    }

    public function delete($product)
    {
        $product->media()->delete();
        $product->seo()->delete();
        $product->categories()->detach();
        $product->brands()->detach();
        $product->colors()->delete();
        $product->relatedProducts()->detach();

        $product->delete();
    }
}
