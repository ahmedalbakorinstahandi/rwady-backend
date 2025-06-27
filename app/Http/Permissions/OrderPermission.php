<?php

namespace App\Http\Permissions;

use App\Models\User;
use App\Services\MessageService;

class OrderPermission
{
    public static function filterIndex($query)
    {
        $user = User::auth();

        if ($user && $user->isCustomer()) {
            $query->where('user_id', $user->id);
        }

        $query->where(function($query) {
            $query->whereDoesntHave('statuses', function($q) {
                $q->where('status', 'paid');
            })
            ->whereNotIn('payment_method', ['qi', 'installment']);
        });

        return $query;
    }

    public static function canShow($order)
    {
        $user = User::auth();

        if ($user && $user->isCustomer()) {
            if ($order->user_id != $user->id) {
                MessageService::abort(403, 'messages.permission.error');
            }
        }
    }

    public static function create($data)
    {
        $user = User::auth();

        if ($user && $user->isCustomer()) {
            $data['user_id'] = $user->id;
        }

        return $data;
    }

    public static function canUpdate($order, $data)
    {
        $user = User::auth();

        if ($user && $user->isCustomer()) {
            if ($order->user_id != $user->id) {
                MessageService::abort(403, 'messages.permission.error');
            }
        }

        return $data;
    }

    public static function canDelete($order)
    {
        $user = User::auth();

        if ($user && $user->isCustomer()) {
            if ($order->user_id != $user->id) {
                MessageService::abort(403, 'messages.permission.error');
            }
        }
    }
}
