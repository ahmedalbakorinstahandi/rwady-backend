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
use App\Traits\UserCacheTrait;

class HomeSectionService
{
    use UserCacheTrait;

    public function getHomeSections(array $filters = [])
    {
        $user = $this->getCurrentUser();
        
        $cacheKey = $this->getUserCacheKey("home_sections_" . md5(serialize($filters)));
        
        // Store cache key for tracking
        $this->storeCacheKey($cacheKey);
        
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
        
        // Clear all home sections cache for all users
        $this->clearAllHomeSectionsCache();
        
        // Clear user auth cache
        User::clearAuthCache();
    }
    
    private function storeCacheKey($cacheKey)
    {
        $keys = cache()->get('cache_keys', []);
        if (!in_array($cacheKey, $keys)) {
            $keys[] = $cacheKey;
            cache()->put('cache_keys', $keys, 60);
        }
    }
    
    private function clearAllHomeSectionsCache()
    {
        // Get all cache keys
        $keys = cache()->get('cache_keys', []);
        
        // Clear only home sections related cache, keep current_user cache
        foreach ($keys as $key) {
            if (str_starts_with($key, 'home_sections_') && !str_starts_with($key, 'current_user')) {
                cache()->forget($key);
            }
        }
    }
}
