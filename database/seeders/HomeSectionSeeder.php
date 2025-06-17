<?php

namespace Database\Seeders;

use App\Models\HomeSection;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HomeSectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sections = [
            [
                'title' => [
                    'en' => 'Banner',
                    'ar' => 'الشريط الإعلاني',
                ],
                'show_title' => false,
                'type' => 'banner',
                'status' => 'static',
                'limit' => null,
                'can_show_more' => false,
                'show_more_path' => null,
                'orders' => 1,
                'is_active' => true,
                'data' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ], // ✅
            [
                'title' => [
                    'en' => 'Category',
                    'ar' => 'الأقسام',
                ],
                'show_title' => true,
                'type' => 'category_list',
                'status' => 'static',
                'limit' => null,
                'can_show_more' => true,
                'show_more_path' => 'categories',
                'orders' => 2,
                'is_active' => true,
                'data' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ], // ✅
            [
                'title' => [
                    'en' => 'Most Sold',
                    'ar' => 'الأكثر مبيعا',
                ],
                'show_title' => true,
                'type' => 'most_sold_products',
                'status' => 'static',
                'limit' => 10,
                'can_show_more' => true,
                'show_more_path' => 'products?most_sold=1',
                'orders' => 3,
                'is_active' => true,
                'data' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ], // ✅ 
            [
                'title' => [
                    'en' => 'Video',
                    'ar' => 'الفيديو',
                ],
                'show_title' => false,
                'type' => 'video',
                'status' => 'static',
                'limit' => null,
                'can_show_more' => false,
                'show_more_path' => null,
                'orders' => 4,
                'is_active' => true,
                'data' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ], // ✅
            [
                'title' => [
                    'en' => 'Recommended',
                    'ar' => 'الموصى به',
                ],
                'show_title' => false,
                'type' => 'recommended_products',
                'status' => 'static',
                'limit' => 10,
                'can_show_more' => true,
                'show_more_path' => 'products?is_recommended=1',
                'orders' => 5,
                'is_active' => true,
                'data' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ], // ✅
            [
                'title' => [
                    'en' => 'New Products',
                    'ar' => 'المنتجات الجديدة',
                ],
                'show_title' => true,
                'type' => 'new_products',
                'status' => 'static',
                'limit' => 10,
                'can_show_more' => true,
                'show_more_path' => 'products?new=1',
                'orders' => 6,
                'is_active' => true,
                'data' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ], // ✅
            [
                'title' => [
                    'en' => 'Featured',
                    'ar' => 'المميز',
                ],
                'show_title' => false,
                'type' => 'featured_sections',
                'status' => 'static',
                'limit' => 10,
                'can_show_more' => false,
                'show_more_path' => null,
                'orders' => 7,
                'is_active' => true,
                'data' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ], // ✅
            [
                'title' => [
                    'en' => 'Brand',
                    'ar' => 'الماركات',
                ],
                'show_title' => true,
                'type' => 'brand_list',
                'status' => 'static',
                'limit' => null,
                'can_show_more' => true,
                'show_more_path' => 'brands',
                'orders' => 8,
                'is_active' => true,
                'data' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ], // ✅

        ];

        foreach ($sections as $section) {
            HomeSection::updateOrCreate(
                ['type' => $section['type']],
                $section
            );
        }
    }
}
