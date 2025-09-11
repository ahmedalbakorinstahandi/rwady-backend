<?php

namespace App\Http\Controllers;

use App\Http\Permissions\OrderPaymentPermission;
use App\Http\Requests\OrderPayment\CreateOrderPaymentRequest;
use App\Http\Requests\OrderPayment\UpdateOrderPaymentRequest;
use App\Http\Resources\OrderPaymentResource;
use App\Http\Services\OrderPaymentService;
use App\Http\Services\OrderService;
use App\Services\ResponseService;
use Illuminate\Http\Request;

class OrderPaymentController extends Controller
{
    private $orderPaymentService;

    public function __construct(OrderPaymentService $orderPaymentService)
    {
        $this->orderPaymentService = $orderPaymentService;
    }

    public function index(Request $request)
    {
        $orderPayments = $this->orderPaymentService->index($request->all());

        return ResponseService::response(
            [
                'success' => true,
                'data' => $orderPayments,
                'resource' => OrderPaymentResource::class,
                'meta' => true,
            ],
            200,
        );
    }

    public function show($id)
    {
        $orderPayment = $this->orderPaymentService->show($id);

        return ResponseService::response([
            'success' => true,
            'data' => $orderPayment,
            'resource' => OrderPaymentResource::class,
            'status' => 200,
        ]);
    }

    public function create(CreateOrderPaymentRequest $request)
    {

        $orderPayment = $this->orderPaymentService->create($request->validated());

        return ResponseService::response([
            'success' => true,
            'data' => $orderPayment,
            'resource' => OrderPaymentResource::class,
            'status' => 200,
        ]);
    }

    public function update($id, UpdateOrderPaymentRequest $request)
    {

        $orderPayment = $this->orderPaymentService->show($id);

        $orderPayment = OrderPaymentPermission::update($orderPayment);

        $orderPayment = $this->orderPaymentService->update($orderPayment, $request->validated());

        return ResponseService::response([
            'success' => true,
            'data' => $orderPayment,
            'resource' => OrderPaymentResource::class,
            'status' => 200,
        ]);
    }

    public function delete($id)
    {
        $orderPayment = $this->orderPaymentService->show($id);

        $orderPayment = OrderPaymentPermission::delete($orderPayment);


        $this->orderPaymentService->delete($orderPayment);

        return ResponseService::response([
            'success' => true,
            'message' => 'messages.order_payment.deleted_successfully',
            'status' => 200,
        ]);
    }
}
