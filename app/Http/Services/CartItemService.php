<?php

namespace App\Http\Services;

use App\Http\Permissions\CartItemPermission;
use App\Models\CartItem;
use App\Services\FilterService;
use App\Services\MessageService;
use Illuminate\Support\Facades\DB;

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
        
        $searchCartItem = CartItem::where('user_id', $data['user_id'])
        ->where('product_id', $data['product_id'])
        ->where('color_id', $data['color_id'])
        ->first();
        
        $cartItem = CartItem::create($data);

        if ($searchCartItem) {
            MessageService::abort(400, 'messages.cart_item.already_in_cart');
        }

        $cartItem = CartItem::where('user_id', $data['user_id'])->where('product_id', $data['product_id'])->where('color_id', $data['color_id'])->first();

        $cartItem->load('product.media', 'product.colors', 'product.categories', 'product.brands', 'color');

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
