<?php

namespace App\Models;

use App\Http\Resources\BannerResource;
use App\Http\Resources\BrandResource;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\FeaturedSectionResource;
use App\Http\Resources\ProductResource;
use App\Http\Resources\SettingResource;
use App\Http\Services\BannerService;
use App\Http\Services\CategoryService;
use App\Http\Services\FeaturedSectionService;
use App\Http\Services\ProductService;
use App\Http\Services\BrandService;
use App\Traits\LanguageTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class HomeSection extends Model
{
    use SoftDeletes, LanguageTrait, HasTranslations;

    public $translatable = ['title'];

    protected $fillable = [
        'title',
        'show_title',
        'type',
        'item_id',
        'status',
        'limit',
        'can_show_more',
        'show_more_path',
        'orders',
        'availability',
        'data'
    ];

    protected $casts = [
        'data' => 'array',
        'availability' => 'boolean',
        'show_title' => 'boolean',
        'can_show_more' => 'boolean',
        'show_more_path' => 'string',
        'limit' => 'integer',
        'orders' => 'integer',
        'item_id' => 'integer',
        'status' => 'string',
        'type' => 'string',
    ];


    protected function title(): Attribute
    {
        return Attribute::make(
            get: fn(string $value) => $this->getAllTranslations('title'),
        );
    }
    public function getShowMorePathAttribute()
    {
        return $this->can_show_more ? $this->attributes['show_more_path'] : null;
    }


    protected function data(): Attribute
    {
        return Attribute::make(
            get: fn(string $value) => json_decode($value, true),
            set: fn($value) => json_encode($value),
        );
    }


    public function getHomeSectionData()
    {
        // Use cache to avoid repeated database queries
        $cacheKey = "home_section_data_{$this->id}_{$this->type}_{$this->item_id}";
        
        return cache()->remember($cacheKey, 300, function () { // Cache for 5 minutes
            return $this->loadHomeSectionData();
        });
    }

    private function loadHomeSectionData()
    {
        switch ($this->type) {
            case 'banner':
                return $this->loadBannerData();
            case 'category_list':
                return $this->loadCategoryData();
            case 'featured_sections':
                return $this->loadFeaturedSectionData();
            case 'category_products':
                return $this->loadCategoryProductsData();
            case 'brand_list':
                return $this->loadBrandData();
            case 'brand_products':
                return $this->loadBrandProductsData();
            case 'recommended_products':
                return $this->loadRecommendedProductsData();
            case 'new_products':
                return $this->loadNewProductsData();
            case 'most_sold_products':
                return $this->loadMostSoldProductsData();
            case 'video':
                return $this->loadVideoData();
            default:
                return null;
        }
    }

    private function loadBannerData()
    {
        $bannerService = new BannerService();
        $banners = $bannerService->index(['limit' => min($this->limit, 10)]);
        return BannerResource::collection($banners);
    }

    private function loadCategoryData()
    {
        $categoryService = new CategoryService();
        $categories = $categoryService->index(['limit' => min($this->limit, 10)]);
        return CategoryResource::collection($categories);
    }

    private function loadFeaturedSectionData()
    {
        $featuredSectionService = new FeaturedSectionService();
        $featuredSections = $featuredSectionService->index(['limit' => min($this->limit, 10)]);
        return FeaturedSectionResource::collection($featuredSections);
    }

    private function loadCategoryProductsData()
    {
        $productService = new ProductService();
        $products = $productService->index([
            'limit' => min($this->limit, 10), 
            'category_id' => $this->item_id
        ]);
        return ProductResource::collection($products);
    }

    private function loadBrandData()
    {
        $brandService = new BrandService();
        $brands = $brandService->index(['limit' => min($this->limit, 10)]);
        return BrandResource::collection($brands);
    }

    private function loadBrandProductsData()
    {
        $productService = new ProductService();
        $products = $productService->index([
            'limit' => min($this->limit, 10), 
            'brand_id' => $this->item_id
        ]);
        return ProductResource::collection($products);
    }

    private function loadRecommendedProductsData()
    {
        $productService = new ProductService();
        $products = $productService->index([
            'limit' => min($this->limit, 10), 
            'is_recommended' => 1
        ]);
        return ProductResource::collection($products);
    }

    private function loadNewProductsData()
    {
        $productService = new ProductService();
        $products = $productService->index([
            'limit' => min($this->limit, 10), 
            'sort_order' => 'desc', 
            'sort_field' => 'created_at'
        ]);
        return ProductResource::collection($products);
    }

    private function loadMostSoldProductsData()
    {
        $productService = new ProductService();
        $products = $productService->index([
            'limit' => min($this->limit, 10), 
            'most_sold' => 1
        ]);
        return ProductResource::collection($products);
    }

    private function loadVideoData()
    {
        // Use single query with whereIn to get both settings at once
        $settings = Setting::whereIn('key', [
            'video_url', 
            'cover_image_url_for_home_page_video'
        ])->pluck('value', 'key');
        
        return [
            'video_url' => $settings['video_url'] ?? null,
            'cover_image_url_for_home_page_video' => $settings['cover_image_url_for_home_page_video'] ?? null,
        ];
    }
}
