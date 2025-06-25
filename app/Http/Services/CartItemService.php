<?php

namespace App\Http\Services;

use App\Http\Permissions\CartItemPermission;
use App\Models\CartItem;
use App\Services\FilterService;
use App\Services\MessageService;

class CartItemService
{
    public function index(array $filters = [])
    {
        $query = CartItem::query()->with('product.media', 'product.colors', 'product.categories', 'product.brands', 'color');

        $searchFields = ['product.name', 'product.description'];
        $numericFields = ['quantity'];
        $dateFields = ['created_at'];
        $exactMatchFields = ['user_id', 'product_id'];
        $inFields = ['product_id', 'user_id'];

        $query = CartItemPermission::filterIndex($query);

        $query = FilterService::applyFilters(
            $query,
            $filters,
            $searchFields,
            $numericFields,
            $dateFields,
            $exactMatchFields,
            $inFields
        );

        return $query;
    }

    public function show(int $id)
    {
        $cartItem = CartItem::where('id', $id)->first();

        if (!$cartItem) {
            MessageService::abort(404, 'messages.cart_item.not_found');
        }

        $cartItem->load('product.media', 'product.colors', 'product.categories', 'product.brands', 'color');

        return $cartItem;
    }

    public function create($data)
    {
        $cartItem = CartItem::create($data);

        $cartItem->load('product.media', 'product.colors', 'product.categories', 'product.brands');


        return $cartItem;
    }



    public function update($data, $cartItem)
    {
        $cartItem->update($data);

        $cartItem->load('product.media', 'product.colors', 'product.categories', 'product.brands', 'color');

        return $cartItem;
    }

    public function delete($cartItem)
    {
        $cartItem->delete();

        $cartItem->load('product.media', 'product.colors', 'product.categories', 'product.brands', 'color');

        return $cartItem;
    }
}
