<?php

namespace App\Http\Services;

use App\Http\Permissions\ProductPermission;
use App\Models\Product;
use App\Models\User;
use App\Services\FilterService;
use App\Services\LanguageService;
use App\Services\MessageService;
use App\Services\OrderHelper;
use Illuminate\Support\Str;


class ProductService
{
    public function index(array $filters = [])
    {
        // Create cache key based on filters
        $cacheKey = "products_" . md5(serialize($filters));
        
        return cache()->remember($cacheKey, 60, function () use ($filters) {
            $query = Product::query()->with(['media', 'colors', 'categories', 'brands']);

            $filters['sort_field'] = 'orders';
            $filters['sort_order'] =  $filters['sort_order'] ?? 'asc';

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

            if (isset($filters['is_favorite']) && $filters['is_favorite'] == true) {
                // Cache user auth to avoid repeated queries
                $user = cache()->remember('current_user', 60, function () {
                    return User::auth();
                });
                
                if ($user) {
                    $query->whereHas('favorites', function ($query) use ($user) {
                        $query->where('user_id', $user->id);
                    });
                }
            }

            if (isset($filters['is_new'])) {
                $query->orderBy('created_at', 'desc');
            }

            // color
            if (isset($filters['color'])) {
                $query->whereHas('colors', function ($query) use ($filters) {
                    $query->where('color', $filters['color']);
                });
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
        });
    }

    public function show(int $id)
    {
        $product = Product::where('id', $id)->first();

        if (!$product) {
            MessageService::abort(404, 'messages.product.not_found');
        }

        $product->load(['brands', 'colors', 'relatedProducts', 'categories', 'media', 'seo', 'relatedCategory']);

        return $product;
    }

    private function handleMedia($product, $mediaArray)
    {
        if (empty($mediaArray)) {
            return;
        }

        $mediaData = array_map(function ($item) {
            $isVideo = filter_var($item, FILTER_VALIDATE_URL) && str_contains($item, 'https');

            return [
                'path' => $item,
                'type' => $isVideo ? 'video' : 'image',
                'source' => $isVideo ? 'link' : 'file',
                'orders' => 0,
            ];
        }, $mediaArray);

        $media = $product->media()->createMany($mediaData);
        foreach ($media as $item) {
            OrderHelper::assign($item);
        }
    }

    private function updateMedia($product, $mediaArray)
    {
        // Get existing media
        $existingMedia = $product->media()->get();
        $existingPaths = $existingMedia->pluck('path')->toArray();

        // Get new media paths
        $newPaths = $mediaArray ?? [];

        // Find items to delete (exist in DB but not in new array)
        $pathsToDelete = array_diff($existingPaths, $newPaths);
        if (!empty($pathsToDelete)) {
            $product->media()->whereIn('path', $pathsToDelete)->delete();
        }

        // Find items to add (exist in new array but not in DB)
        $pathsToAdd = array_diff($newPaths, $existingPaths);
        if (!empty($pathsToAdd)) {
            $this->handleMedia($product, $pathsToAdd);
        }
    }

    public function create($data)
    {
        $data = LanguageService::prepareTranslatableData($data, new Product);

        // Generate SKU if not provided
        if (!isset($data['sku']) || empty($data['sku'])) {
            $data['sku'] = 'SKU-' . strtoupper(uniqid());
        }

        $data['stock_unlimited'] = $data['stock_unlimited'] ?? false;



        $product = Product::create($data);

        OrderHelper::assign($product);

        $product->sku = $product->id;
        $product->save();

        // Handle media (images and videos in one array)
        if (isset($data['media'])) {
            $this->handleMedia($product, $data['media']);
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

        $product->stock_unlimited = $data['stock_unlimited'] ??  $product->stock_unlimited ?? false;

        $product->update($data);

        // Handle media (images and videos in one array)
        if (isset($data['media'])) {
            $this->updateMedia($product, $data['media']);
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

        // Clear cache after update
        $this->clearProductCache();

        return $product;
    }

    public function delete($product)
    {
        $product->delete();

        // Clear cache after delete
        $this->clearProductCache();

        return $product;
    }

    private function clearProductCache()
    {
        // Clear all product-related cache
        cache()->flush();
        
        // Clear user auth cache
        User::clearAuthCache();
    }

    public function toggleFavorite($product)
    {
        $user = User::auth();
        if ($user) {
            $user->favorites()->toggle($product->id);
        }

        return $user->favorites()->where('product_id', $product->id)->exists();
    }

    // reorder
    public function reorder($product, $data)
    {
        OrderHelper::reorder($product, $data['orders']);

        return $product;
    }


    // reorder media
    public function reorderMedia($media, $data)
    {
        OrderHelper::reorder($media, $data['orders']);

        return $media;
    }
}
