<?php

namespace App\Http\Services;

use App\Models\Banner;
use App\Models\HomeSection;
use App\Models\Setting;

class HomeSectionService
{
    public function getHomeSections()
    {
        $homeSections = HomeSection::all();

        $homeSections = $homeSections->map(function ($homeSection) {

            if ($homeSection->type === 'banner') {
                $bannerService = new BannerService();
                $homeSection->data = $bannerService->index(['limit' => $homeSection->limit]);
            }

            if ($homeSection->type === 'category_list') {
                $categoryService = new CategoryService();
                $homeSection->data = $categoryService->index(['limit' => $homeSection->limit]);
            }

            if ($homeSection->type === 'featured_section') {
                $featuredSectionService = new FeaturedSectionService();
                $homeSection->data = $featuredSectionService->index(['limit' => $homeSection->limit]);
            }

            if ($homeSection->type === 'category_products') {
                $productService = new ProductService();
                $homeSection->data = $productService->index(['limit' => $homeSection->limit, 'category_id' => $homeSection->item_id]);
            }

            if ($homeSection->type === 'brand_list') {
                $brandService = new BrandService();
                $homeSection->data = $brandService->index(['limit' => $homeSection->limit]);
            }

            if ($homeSection->type === 'brand_products') {
                $productService = new ProductService();
                $homeSection->data = $productService->index(['limit' => $homeSection->limit, 'brand_id' => $homeSection->item_id]);
            }

            if ($homeSection->type === 'recommended_products') {
                $productService = new ProductService();
                $homeSection->data = $productService->index(['limit' => $homeSection->limit, 'is_recommended' => 1]);
            }

            if ($homeSection->type === 'new_products') {
                $productService = new ProductService();
                $homeSection->data = $productService->index(['limit' => $homeSection->limit, 'sort_order' => 'desc', 'sort_field' => 'created_at']);
            }

            if ($homeSection->type === 'most_sold_products') {
                $productService = new ProductService();
                $homeSection->data = $productService->index(['limit' => $homeSection->limit, 'most_sold' => 1]);
            }

            if ($homeSection->type === 'video') {
                $homeSection->data = Setting::where('key', 'video_url')->first();
            }
        });


        return $homeSections;
    }
}
