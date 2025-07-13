<?php

namespace App\Http\Permissions;

use App\Models\User;

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
}
