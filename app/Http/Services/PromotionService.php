<?php

namespace App\Http\Services;

use App\Http\Permissions\PromotionPermission;
use App\Models\Promotion;
use App\Services\FilterService;

class PromotionService
{
    public function index($filters = [])
    {
        $qury = Promotion::query();

        $qury = PromotionPermission::filterIndex($qury);

        $promottions = FilterService::applyFilters(
            $qury,
            $filters,
            ['title'],
            ['discount_value', 'min_cart_total'],
            ['start_at', 'end_at', 'created_at'],
            ['type', 'discount_type', 'status'],
            ['type', 'discount_type', 'status'],
        );


        return $promottions;
    }
}
