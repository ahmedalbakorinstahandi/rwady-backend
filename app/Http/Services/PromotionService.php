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
            false,
        );

        $promotionsTypeOne = clone $promotions->get();
        $promotionsTypeTwo = clone $promotions->get();
        $promotionsTypeThree = clone $promotions->get();

        $promotionsTypeOne->where('type', 'product')->orWhere('type', 'category');
        $promotionsTypeTwo->where('type', 'cart_total') ->latest();
        $promotionsTypeThree->where('type', 'shipping') ->latest();

        $promotions = $promotionsTypeOne->merge($promotionsTypeTwo)->merge($promotionsTypeThree);

        return $promotions->paginate($filters['limit'] ?? 20);
    }

    public function show($id)
    {
        $promotion = Promotion::with(['categories', 'products'])->where('id', $id)->first();

        if (!$promotion) {
            MessageService::abort(404, 'messages.promotion.not_found');
        }

        PromotionPermission::show($promotion);

        return $promotion;
    }

    public function create($data)
    {
        $data = LanguageService::prepareTranslatableData($data, new Promotion);


        // if type is shipping, set discount_type to fixed and discount_value to 0
        if ($data['type'] == 'shipping') {
            $data['discount_type'] = 'percentage';
            $data['discount_value'] = 100;
        }

        $promotion = Promotion::create($data);

        if (isset($data['categories'])) {
            $categoryData = array_fill_keys($data['categories'], []);
            $promotion->categories()->sync($categoryData);
        }

        if (isset($data['products'])) {
            $productData = array_fill_keys($data['products'], []);
            $promotion->products()->sync($productData);
        }


        $promotion = $this->show($promotion->id);

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

        $promotion = $this->show($promotion->id);


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
