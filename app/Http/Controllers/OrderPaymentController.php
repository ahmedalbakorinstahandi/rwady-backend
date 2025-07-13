<?php

namespace App\Http\Controllers;

use App\Http\Resources\OrderPaymentResource;
use App\Http\Services\OrderPaymentService;
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
}
