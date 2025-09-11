<?php

namespace App\Http\Permissions;

use App\Models\User;
use App\Services\MessageService;

class OrderPaymentPermission
{
    public static function index($query)
    {
        $user = User::auth();

        if (!$user->isAdmin()) {
            $query->whereHas('order', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            });
        }

        return $query;
    }

    public static function update($orderPayment)
    {

        if ($orderPayment->method != 'transfer' || $orderPayment->method != 'cash') {
            MessageService::abort(403, 'messages.order_payment.update_not_allowed');
        }

        return true;
    }

    public static function delete($orderPayment)
    {
        if ($orderPayment->created_by != 'admin') {
            MessageService::abort(403, 'messages.order_payment.delete_not_allowed');
        }

        return true;
    }
}
