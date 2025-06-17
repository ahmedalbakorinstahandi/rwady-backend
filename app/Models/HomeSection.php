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
        if ($this->type === 'banner') {
            $bannerService = new BannerService();
            $banners = $bannerService->index(['limit' => $this->limit]);
            $this->data = BannerResource::collection($banners);
        }

        if ($this->type === 'category_list') {
            $categoryService = new CategoryService();
            $categories = $categoryService->index(['limit' => $this->limit]);
            $this->data = CategoryResource::collection($categories);
        }

        if ($this->type === 'featured_section') {
            $featuredSectionService = new FeaturedSectionService();
            $featuredSections = $featuredSectionService->index(['limit' => $this->limit]);
            $this->data = FeaturedSectionResource::collection($featuredSections);
        }

        if ($this->type === 'category_products') {
            $productService = new ProductService();
            $products = $productService->index(['limit' => $this->limit, 'category_id' => $this->item_id]);
            $this->data = ProductResource::collection($products);
        }

        if ($this->type === 'brand_list') {
            $brandService = new BrandService();
            $brands = $brandService->index(['limit' => $this->limit]);
            $this->data = BrandResource::collection($brands);
        }

        if ($this->type === 'brand_products') {
            $productService = new ProductService();
            $products = $productService->index(['limit' => $this->limit, 'brand_id' => $this->item_id]);
            $this->data = ProductResource::collection($products);
        }

        if ($this->type === 'recommended_products') {
            $productService = new ProductService();
            $products = $productService->index(['limit' => $this->limit, 'is_recommended' => 1]);
            $this->data = ProductResource::collection($products);
        }

        if ($this->type === 'new_products') {
            $productService = new ProductService();
            $products = $productService->index(['limit' => $this->limit, 'sort_order' => 'desc', 'sort_field' => 'created_at']);
            $this->data = ProductResource::collection($products);
        }

        if ($this->type === 'most_sold_products') {
            $productService = new ProductService();
            $products = $productService->index(['limit' => $this->limit, 'most_sold' => 1]);
            $this->data = ProductResource::collection($products);
        }

        if ($this->type === 'video') {
            $videoUrl = Setting::where('key', 'video_url')->first();
            $this->data = new SettingResource($videoUrl);
        }

        return $this;
    }
}
