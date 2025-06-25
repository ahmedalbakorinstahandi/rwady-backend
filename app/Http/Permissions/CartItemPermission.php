<?php

namespace App\Http\Permissions;

use App\Models\User;
use App\Services\MessageService;

class CartItemPermission
{
    public static function filterIndex($query)
    {
        $user = User::auth();

        if ($user) {
            $query->where('user_id', $user->id);
        } else {
            $query->where('user_id', 0);
        }

        return $query;
    }

    public static function canShow($cartItem)
    {
        $user = User::auth();

        if ($user->id !== $cartItem->user_id) {
            MessageService::abort(403, 'messages.permission.error');
        }
    }   

    public static function create($data)
    {
        $user = User::auth();

        $data['user_id'] = $user->id;

        return $data;
    }

    public static function canUpdate($cartItem)
    {
        $user = User::auth();

        if ($user->id !== $cartItem->user_id) {
            MessageService::abort(403, 'messages.permission.error');
        }
    }

    public static function canDelete($cartItem)
    {
        $user = User::auth();

        if ($user->id !== $cartItem->user_id) {
            MessageService::abort(403, 'messages.permission.error');
        }
    }
}
