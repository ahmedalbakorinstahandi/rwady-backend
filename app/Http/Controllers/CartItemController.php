<?php

namespace App\Http\Controllers;

use App\Http\Permissions\CartItemPermission;
use App\Http\Requests\CartItem\CreateCartItemRequest;
use App\Http\Requests\CartItem\UpdateCartItemRequest;
use App\Http\Resources\CartItemResource;
use App\Http\Services\CartItemService;
use App\Services\ResponseService;
use Illuminate\Http\Request;

class CartItemController extends Controller
{
    protected $cartItemService;

    public function __construct(CartItemService $cartItemService)
    {
        $this->cartItemService = $cartItemService;
    }

    public function index(Request $request)
    {
        $cartItems = $this->cartItemService->index($request->all());

        return ResponseService::response([
            'success' => true,
            'data' => $cartItems,
            'meta' => true,
            'resource' => CartItemResource::class,
            'status' => 200,
        ]);
    }

    public function show(int $id)
    {
        $cartItem = $this->cartItemService->show($id);

        CartItemPermission::canShow($cartItem);

        return ResponseService::response([
            'success' => true,
            'data' => $cartItem,
            'resource' => CartItemResource::class,
            'status' => 200,
        ]);
    }

    public function create(CreateCartItemRequest $request)
    {

        $data = CartItemPermission::create($request->validated());

        $cartItem = $this->cartItemService->create($data);

        return ResponseService::response([
            'success' => true,
            'data' => $cartItem,
            'resource' => CartItemResource::class,
            'message' => trans('messages.cart_item.created'),
            'status' => 200,
        ]);
    }

    public function update(UpdateCartItemRequest $request, int $id)
    {

        $cartItem = $this->cartItemService->show($id);

        CartItemPermission::canUpdate($cartItem);

        $cartItem = $this->cartItemService->update($request->validated(), $cartItem);

        return ResponseService::response([
            'success' => true,
            'data' => $cartItem,
            'resource' => CartItemResource::class,
            'message' => trans('messages.cart_item.updated'),
            'status' => 200,
        ]);
    }

    public function delete(int $id)
    {
        $cartItem = $this->cartItemService->show($id);

        CartItemPermission::canDelete($cartItem);

        $this->cartItemService->delete($cartItem);

        return ResponseService::response([
            'success' => true,
            'message' => trans('messages.cart_item.deleted'),
            'status' => 200,
        ]);
    }
}
