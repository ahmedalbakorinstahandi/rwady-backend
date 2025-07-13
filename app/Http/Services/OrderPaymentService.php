<?php

namespace App\Http\Services;

use App\Http\Permissions\OrderPaymentPermission;
use App\Models\OrderPayment;
use App\Services\FilterService;

class OrderPaymentService
{
    public function index($data)
    {
        $query = OrderPayment::query();

        $query = OrderPaymentPermission::index($query);

        $orderPayments = FilterService::applyFilters(
            $query,
            $data,
            ['description'],
            ['order_id', 'amount'],
            [],
            ['order_id', 'status', 'is_refund', 'method'],
            ['order_id', 'status', 'method'],
        );



        return $orderPayments;
    }
}
