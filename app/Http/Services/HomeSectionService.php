<?php

namespace App\Http\Services;

use App\Http\Resources\BannerResource;
use App\Http\Resources\BrandResource;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\FeaturedSectionResource;
use App\Http\Resources\ProductResource;
use App\Http\Resources\SettingResource;
use App\Models\Banner;
use App\Models\HomeSection;
use App\Models\Setting;
use App\Services\MessageService;
use App\Services\OrderHelper;

class HomeSectionService
{
    public function getHomeSections()
    {
        $homeSections = HomeSection::all();

        $homeSections = $homeSections->map(function ($homeSection) {

            if ($homeSection->type === 'banner') {
                $bannerService = new BannerService();
                $banners = $bannerService->index(['limit' => $homeSection->limit]);
                $homeSection->data = BannerResource::collection($banners);
            }

            if ($homeSection->type === 'category_list') {
                $categoryService = new CategoryService();
                $categories = $categoryService->index(['limit' => $homeSection->limit]);
                $homeSection->data = CategoryResource::collection($categories);
            }

            if ($homeSection->type === 'featured_section') {
                $featuredSectionService = new FeaturedSectionService();
                $featuredSections = $featuredSectionService->index(['limit' => $homeSection->limit]);
                $homeSection->data = FeaturedSectionResource::collection($featuredSections);
            }

            if ($homeSection->type === 'category_products') {
                $productService = new ProductService();
                $products = $productService->index(['limit' => $homeSection->limit, 'category_id' => $homeSection->item_id]);
                $homeSection->data = ProductResource::collection($products);
            }

            if ($homeSection->type === 'brand_list') {
                $brandService = new BrandService();
                $brands = $brandService->index(['limit' => $homeSection->limit]);
                $homeSection->data = BrandResource::collection($brands);
            }

            if ($homeSection->type === 'brand_products') {
                $productService = new ProductService();
                $products = $productService->index(['limit' => $homeSection->limit, 'brand_id' => $homeSection->item_id]);
                $homeSection->data = ProductResource::collection($products);
            }

            if ($homeSection->type === 'recommended_products') {
                $productService = new ProductService();
                $products = $productService->index(['limit' => $homeSection->limit, 'is_recommended' => 1]);
                $homeSection->data = ProductResource::collection($products);
            }

            if ($homeSection->type === 'new_products') {
                $productService = new ProductService();
                $products = $productService->index(['limit' => $homeSection->limit, 'sort_order' => 'desc', 'sort_field' => 'created_at']);
                $homeSection->data = ProductResource::collection($products);
            }

            if ($homeSection->type === 'most_sold_products') {
                $productService = new ProductService();
                $products = $productService->index(['limit' => $homeSection->limit, 'most_sold' => 1]);
                $homeSection->data = ProductResource::collection($products);
            }

            if ($homeSection->type === 'video') {
                $videoUrl = Setting::where('key', 'video_url')->first();
                $homeSection->data = new SettingResource($videoUrl);
            }

            return $homeSection;
        });


        return $homeSections;
    }

    public function show($id)
    {
        $homeSection = HomeSection::where('id', $id)->first();

        if (!$homeSection) {
            MessageService::abort(404, 'messages.home_section.not_found');
        }

        return $homeSection;
    }

    public function reorder($homeSection, $validatedData)
    {
        OrderHelper::reorder($homeSection, $validatedData['orders']);

        return $homeSection;
    }
}
