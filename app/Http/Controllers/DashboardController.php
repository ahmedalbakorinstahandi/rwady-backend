<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\ResponseService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function analytics()
    {

        $orders = Order::all();

        $totalRevenue = 0;
        foreach ($orders as $order) {
            $totalRevenue += $order->getTotalAmountAttribute();
        }

        $data = [
            'total_orders' => Order::count(),
            'total_products' => Product::count(),
            'total_revenue' => $totalRevenue,
            'total_customers' => User::where('role', 'customer')->count(),
        ];

        return ResponseService::response(
            [
                'success' => true,
                'data' => $data,
            ],
            200
        );
    }
}
