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
use App\Models\User;
use App\Services\FilterService;
use App\Services\MessageService;
use App\Services\OrderHelper;

class HomeSectionService
{
    public function getHomeSections(array $filters = [])
    {
        // Cache user auth to avoid repeated queries
        $user = cache()->remember('current_user', 60, function () {
            return User::auth();
        });
        
        $cacheKey = "home_sections_" . ($user ? $user->id : 'guest') . "_" . md5(serialize($filters));
        
        return cache()->remember($cacheKey, 60, function () use ($user, $filters) {
            $query = HomeSection::query();

            if (!$user || $user->isCustomer()) {
                $query->where('availability', true);
            }

            $filters['sort_field'] = 'orders';
            $filters['sort_order'] = 'asc';
            $filters['limit'] = 100;

            $homeSections = FilterService::applyFilters(
                $query,
                $filters,
                ['title'],
                ['limit'],
                ['created_at', 'updated_at'],
                ['id', 'show_title', 'type', 'item_id', 'status', 'can_show_more', 'orders', 'availability'],
                ['id'],
            );

            return $homeSections;
        });
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


    public function create($data)
    {

        $data['show_title'] = true;
        $data['status'] = 'dynamic';
        $data['can_show_more'] = true;
        if ($data['type'] === 'category_products') {
            $data['show_more_path'] = '/products?category_id=' . $data['item_id'];
        } elseif ($data['type'] === 'brand_products') {
            $data['show_more_path'] = '/products?brand_id=' . $data['item_id'];
        }

        $homeSection = HomeSection::create($data);

        OrderHelper::assign($homeSection);


        return $homeSection;
    }
    public function update($homeSection, $data)
    {
        $homeSection->update($data);
        
        // Clear cache after update
        $this->clearHomeSectionCache($homeSection);
        
        return $homeSection;
    }
    public function delete($homeSection)
    {
        $homeSection->delete();
        
        // Clear cache after delete
        $this->clearHomeSectionCache($homeSection);
        
        return $homeSection;
    }
    
    private function clearHomeSectionCache($homeSection)
    {
        // Clear specific home section cache
        cache()->forget("home_section_data_{$homeSection->id}_{$homeSection->type}_{$homeSection->item_id}");
        
        // Clear all home sections cache
        cache()->flush();
        
        // Clear user auth cache
        User::clearAuthCache();
    }
}
