<?php

namespace App\Http\Controllers;

use App\Http\Permissions\OrderPermission;
use App\Http\Requests\Order\CheckOrderDeailsRequest;
use App\Http\Requests\Order\CreateOrderRequest;
use App\Http\Requests\Order\UpdateOrderRequest;
use App\Http\Resources\OrderResource;
use App\Http\Services\OrderService;
use App\Services\ResponseService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index(Request $request)
    {
        $orders = $this->orderService->index($request->all());

        return ResponseService::response([
            'success' => true,
            'data' => $orders,
            'resource' => OrderResource::class,
            'status' => 200,
            'meta' => true,
        ]);
    }

    public function show($id)
    {
        $order = $this->orderService->show($id);

        return ResponseService::response([
            'success' => true,
            'data' => $order,
            'resource' => OrderResource::class,
            'status' => 200,
        ]);
    }

    public function create(CreateOrderRequest $request)
    {
        $data = OrderPermission::create($request->validated());

        $order = $this->orderService->create($data);

        return ResponseService::response([
            'success' => true,
            'data' => $order,
            'message' => 'messages.order.created_successfully',
            'resource' => OrderResource::class,
            'status' => 200,
        ]);
    }

    public function checkOrderDetails(CheckOrderDeailsRequest $request)
    {
        $data = $this->orderService->checkOrderDetails($request->validated());

        return ResponseService::response([
            'success' => true,
            'data' => $data,
            'message' => 'messages.order.details_checked_successfully',
            'resource' => OrderResource::class,
            'status' => 200,
        ]);
    }

    public function update(UpdateOrderRequest $request, $id)
    {
        $order = $this->orderService->show($id);

        $data = OrderPermission::canUpdate($order, $request->validated());

        $order = $this->orderService->update($order, $data);

        return ResponseService::response([
            'success' => true,
            'data' => $order,
            'message' => 'messages.order.updated_successfully',
            'resource' => OrderResource::class,
            'status' => 200,
        ]);
    }

    public function delete($id)
    {
        $order = $this->orderService->show($id);

        OrderPermission::canDelete($order);

        $this->orderService->delete($order);

        return ResponseService::response([
            'success' => true,
            'message' => 'messages.order.deleted_successfully',
            'status' => 200,
        ]);
    }
}
