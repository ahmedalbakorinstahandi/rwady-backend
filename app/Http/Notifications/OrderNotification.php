<?php

namespace App\Http\Notifications;

use App\Models\Order;
use App\Models\User;
use App\Services\FirebaseService;

class OrderNotification
{
    public static function newOrder($order)
    {
        $users = User::where('role', 'admin')->get();

        $title = 'notifications.admin.order.new.title';
        $body = 'notifications.admin.order.new.body';

        $replace = [
            'order_id' => $order->id,
            'user_name' => $order->user->first_name . ' ' . $order->user->last_name,
        ];

        FirebaseService::sendToTokensAndStorage(
            $users->pluck('id'),
            [
                'id' => $order->id,
                'type' => 'order',
            ],
            $title,
            $body,
            $replace,
            $replace,
        );
    }

    // update order status
    public static function updateOrderStatus($order)
    {

        $status = $order->statuses->last()->status;

        $users = User::where('role', 'admin')->get();

        // "pending", "in_progress", "shipping", "completed", "cancelled"
        switch ($status) {
            case 'pending':
                $title = 'notifications.admin.order.status.pending.title';
                $body = 'notifications.admin.order.status.pending.body';
                break;
            case 'in_progress':
                $title = 'notifications.admin.order.status.in_progress.title';
                $body = 'notifications.admin.order.status.in_progress.body';
                break;
            case 'shipping':
                $title = 'notifications.admin.order.status.shipping.title';
                $body = 'notifications.admin.order.status.shipping.body';
                break;
            case 'completed':
                $title = 'notifications.admin.order.status.completed.title';
                $body = 'notifications.admin.order.status.completed.body';
                break;
            case 'cancelled':
                $title = 'notifications.admin.order.status.cancelled.title';
                $body = 'notifications.admin.order.status.cancelled.body';
                break;
        }



        $replace = [
            'order_id' => $order->id,
            'user_name' => $order->user->first_name . ' ' . $order->user->last_name,
            'status' => $status,
        ];

        FirebaseService::sendToTokensAndStorage(
            $users->pluck('id'),
            [
                'id' => $order->id,
                'type' => 'order',
            ],
            $title,
            $body,
            $replace,
            $replace,
        );
    }
}
