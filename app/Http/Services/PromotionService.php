<?php

namespace App\Http\Services;

use App\Http\Permissions\PromotionPermission;
use App\Models\Promotion;
use App\Services\FilterService;

class PromotionService
{
    public function index($filters = [])
    {
        $query = Promotion::query();

        $query = PromotionPermission::filterIndex($query);

        $promotions = FilterService::applyFilters(
            $query,
            $filters,
            ['title'],
            ['discount_value', 'min_cart_total'],
            ['start_at', 'end_at', 'created_at'],
            ['type', 'discount_type', 'status'],
            ['type', 'discount_type', 'status'],
        );

        return $promotions;
    }
}
