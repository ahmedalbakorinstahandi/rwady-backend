<?php

namespace App\Http\Services;

use App\Http\Permissions\ProductPermission;
use App\Models\Product;
use App\Services\FilterService;
use App\Services\MessageService;
use App\Services\OrderHelper;
use Illuminate\Support\Str;


class ProductService
{
    public function index(array $filters = [])
    {
        $query = Product::query();

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
            'view_in_home',
            'availability‍',
            'stock_unlimited',
            'out_of_stock',
            'shipping_type',
            'related_category_id'
        ];
        $inFields = [];


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
            MessageService::abort(404, 'product.not_found');
        }

        $product->load(['category', 'brand', 'colors', 'relatedProducts', 'categories', 'media', 'seo']);

        return $product;
    }

    public function create($data)
    {

        if (!isset($data['sku'])) {
            $data['sku'] = Str::random(10);
        }


        $product = Product::create($data);

        if (isset($data['images'])) {
            foreach ($data['images'] as $image) {
                $media = $product->media()->create([
                    'path' => $image['path'],
                    'type' => 'image',
                    'source' => 'file',
                    'orders' => 0,
                ]);

                OrderHelper::assign($media);
            }
        }

        if (isset($data['videos'])) {

            foreach ($data['videos'] as $video) {
                $media = $product->media()->create([
                    'path' => $video['link'],
                    'type' => 'video',
                    'source' => 'link',
                    'orders' => 0,
                ]);

                OrderHelper::assign($media);
            }
        }

        if (isset($data['categories'])) {
            foreach ($data['categories'] as $category) {
                $product->categories()->create([
                    'category_id' => $category,
                    'product_id' => $product->id,
                ]);
            }
        }

        if (isset($data['brands'])) {
            foreach ($data['brands'] as $brand) {
                $product->brands()->create([
                    'brand_id' => $brand,
                    'product_id' => $product->id,
                ]);
            }
        }

        if (isset($data['colors'])) {
            foreach ($data['colors'] as $color) {
                $product->colors()->create([
                    'color' => $color,
                ]);
            }
        }

        if (isset($data['related_products'])) {
            foreach ($data['related_products'] as $related_product) {
                $product->relatedProducts()->create([
                    'related_product_id' => $related_product,
                    'product_id' => $product->id,
                ]);
            }
        }

        if (isset($data['seo'])) {
            $product->seo()->create([
                'meta_title' => $data['seo']['meta_title'],
                'meta_description' => $data['seo']['meta_description'],
                'keywords' => $data['seo']['keywords'],
                'image' => $data['seo']['image'],
                'seoable_type' => Product::class,
                'seoable_id' => $product->id,
            ]);
        }

        return $product;
    }
}
