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

        if (empty($data['out_of_stock'])) {
            $data['out_of_stock'] = "show_on_storefront";
        }

        if (empty($data['stock_unlimited'])) {
            $data['stock_unlimited'] = false;
        }

        


        $data = LanguageService::prepareTranslatableData($data, new Product);


        $product = Product::create($data);

        if (isset($data['images'])) {
            foreach ($data['images'] as $image) {
                $media = $product->media()->create([
                    'path' => $image,
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
                    'path' => $video,
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

        $product->load(['category', 'brand', 'colors', 'relatedProducts', 'categories', 'media', 'seo']);

        return $product->fresh();
    }

    public function update($data, $product)
    {
        $data = LanguageService::prepareTranslatableData($data, $product);



        $product->update($data);

        if (isset($data['images'])) {
            $existingImages = $product->media()->where('type', 'image')->pluck('path')->toArray();
            $newImages = $data['images'];

            // Remove images that are no longer present
            $imagesToDelete = array_diff($existingImages, $newImages);
            if (!empty($imagesToDelete)) {
                $product->media()->whereIn('path', $imagesToDelete)->where('type', 'image')->delete();
            }

            // Add only new images that don't exist
            $imagesToAdd = array_diff($newImages, $existingImages);
            foreach ($data['images'] as $image) {
                if (in_array($image, $imagesToAdd)) {
                    $media = $product->media()->create([
                        'path' => $image,
                        'type' => 'image',
                        'source' => 'file',
                        'orders' => 0,
                    ]);
                    OrderHelper::assign($media);
                }
            }
        }

        if (isset($data['videos'])) {
            $existingVideos = $product->media()->where('type', 'video')->pluck('path')->toArray();
            $newVideos = $data['videos'];

            // Remove videos that are no longer present
            $videosToDelete = array_diff($existingVideos, $newVideos);
            if (!empty($videosToDelete)) {
                $product->media()->whereIn('path', $videosToDelete)->where('type', 'video')->delete();
            }

            // Add only new videos that don't exist
            $videosToAdd = array_diff($newVideos, $existingVideos);
            foreach ($data['videos'] as $video) {
                if (in_array($video, $videosToAdd)) {
                    $media = $product->media()->create([
                        'path' => $video,
                        'type' => 'video',
                        'source' => 'link',
                        'orders' => 0,
                    ]);
                    OrderHelper::assign($media);
                }
            }
        }

        if (isset($data['categories'])) {
            $existingCategories = $product->categories()->pluck('category_id')->toArray();
            $newCategories = $data['categories'];

            // Remove categories that are no longer present
            $categoriesToDelete = array_diff($existingCategories, $newCategories);
            if (!empty($categoriesToDelete)) {
                $product->categories()->whereIn('category_id', $categoriesToDelete)->delete();
            }

            // Add only new categories that don't exist
            $categoriesToAdd = array_diff($newCategories, $existingCategories);
            foreach ($categoriesToAdd as $categoryId) {
                $product->categories()->create([
                    'category_id' => $categoryId,
                    'product_id' => $product->id,
                ]);
            }
        }

        if (isset($data['brands'])) {
            $existingBrands = $product->brands()->pluck('brand_id')->toArray();
            $newBrands = $data['brands'];

            // Remove brands that are no longer present
            $brandsToDelete = array_diff($existingBrands, $newBrands);
            if (!empty($brandsToDelete)) {
                $product->brands()->whereIn('brand_id', $brandsToDelete)->delete();
            }

            // Add only new brands that don't exist
            $brandsToAdd = array_diff($newBrands, $existingBrands);
            foreach ($brandsToAdd as $brandId) {
                $product->brands()->create([
                    'brand_id' => $brandId,
                    'product_id' => $product->id,
                ]);
            }
        }

        if (isset($data['colors'])) {
            $existingColors = $product->colors()->pluck('color')->toArray();
            $newColors = $data['colors'];

            // Remove colors that are no longer present
            $colorsToDelete = array_diff($existingColors, $newColors);
            if (!empty($colorsToDelete)) {
                $product->colors()->whereIn('color', $colorsToDelete)->delete();
            }

            // Add only new colors that don't exist
            $colorsToAdd = array_diff($newColors, $existingColors);
            foreach ($colorsToAdd as $color) {
                $product->colors()->create([
                    'color' => $color,
                    'product_id' => $product->id,
                ]);
            }
        }

        if (isset($data['related_products'])) {
            $existingRelatedProducts = $product->relatedProducts()->pluck('related_product_id')->toArray();
            $newRelatedProducts = $data['related_products'];

            // Remove related products that are no longer present
            $relatedProductsToDelete = array_diff($existingRelatedProducts, $newRelatedProducts);
            if (!empty($relatedProductsToDelete)) {
                $product->relatedProducts()->whereIn('related_product_id', $relatedProductsToDelete)->delete();
            }

            // Add only new related products that don't exist
            $relatedProductsToAdd = array_diff($newRelatedProducts, $existingRelatedProducts);
            foreach ($relatedProductsToAdd as $relatedProductId) {
                $product->relatedProducts()->create([
                    'related_product_id' => $relatedProductId,
                    'product_id' => $product->id,
                ]);
            }
        }

        if (isset($data['seo'])) {
            $product->seo()->updateOrCreate([
                'seoable_type' => Product::class,
                'seoable_id' => $product->id,
            ], [
                'meta_title' => $data['seo']['meta_title'],
                'meta_description' => $data['seo']['meta_description'],
                'keywords' => $data['seo']['keywords'],
                'image' => $data['seo']['image'],
            ]);
        }

        $product->load(['category', 'brand', 'colors', 'relatedProducts', 'categories', 'media', 'seo']);

        return $product->fresh();
    }

    public function delete($product)
    {

        $product->media()->delete();
        $product->seo()->delete();
        $product->categories()->delete();
        $product->brands()->delete();
        $product->colors()->delete();
        $product->relatedProducts()->delete();

        $product->delete();
    }
}
