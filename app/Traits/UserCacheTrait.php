<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

trait UserCacheTrait
{
    /**
     * Get current user with caching
     */
    protected function getCurrentUser()
    {
        return User::auth();
    }

    /**
     * Get cache key for current user
     */
    protected function getUserCacheKey($suffix = '')
    {
        $user = $this->getCurrentUser();
        $userId = $user ? $user->id : 'guest';
        return "user_{$userId}_{$suffix}";
    }

    /**
     * Remember data for current user
     */
    protected function rememberForUser($key, $callback, $ttl = 60)
    {
        $cacheKey = $this->getUserCacheKey($key);
        return Cache::remember($cacheKey, $ttl, $callback);
    }

    /**
     * Forget cache for current user
     */
    protected function forgetUserCache($key = '')
    {
        $cacheKey = $this->getUserCacheKey($key);
        Cache::forget($cacheKey);
    }
} 