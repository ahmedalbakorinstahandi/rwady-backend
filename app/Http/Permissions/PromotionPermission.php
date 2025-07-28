<?php

namespace App\Http\Permissions;

use App\Models\User;

class PromotionPermission
{
    public static function filterIndex($query)
    {
        $user = User::auth();

        if (!$user->isAdmin()) {
            return $query->where('status', 'active')
                ->where(function ($q) {
                    $q->whereNull('start_at')
                        ->orWhere('start_at', '<=', now());
                })
                ->where(function ($q) {
                    $q->whereNull('end_at')
                        ->orWhere('end_at', '>=', now());
                });
        }

        return $query;
    }
}
