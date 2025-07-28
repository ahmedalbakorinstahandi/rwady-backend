<?php

namespace App\Http\Services;

use App\Http\Permissions\PromotionPermission;
use App\Models\Promotion;
use App\Services\FilterService;
use App\Services\LanguageService;
use App\Services\MessageService;

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

    public function show($id)
    {
        $promotion = Promotion::with(['categories', 'products'])->where('id', $id)->first();

        if (!$promotion) {
            MessageService::abort(404, 'messages.promotion.not_found');
        }

        return $promotion;
    }

    public function create($data)
    {
        $data = LanguageService::prepareTranslatableData($data, new Promotion);

        $promotion = Promotion::create($data);

        if (isset($data['categories'])) {
            $categoryData = array_fill_keys($data['categories'], []);
            $promotion->categories()->sync($categoryData);
        }

        if (isset($data['products'])) {
            $productData = array_fill_keys($data['products'], []);
            $promotion->products()->sync($productData);
        }

        return $promotion;
    }

    public function  update($promotion, $data)
    {
        $data = LanguageService::prepareTranslatableData($data, $promotion);

        $promotion->update($data);

        if (isset($data['categories'])) {
            $categoryData = array_fill_keys($data['categories'], []);
            $promotion->categories()->sync($categoryData);
        }

        if (isset($data['products'])) {
            $productData = array_fill_keys($data['products'], []);
            $promotion->products()->sync($productData);
        }


        return $promotion;
    }

    public function delete($promotion)
    {
        $promotion->products()->detach();
        $promotion->categories()->detach();
        $promotion->delete();

        return true;
    }
}
