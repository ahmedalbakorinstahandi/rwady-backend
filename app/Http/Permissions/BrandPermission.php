<?php

namespace App\Http\Permissions;

use App\Models\User;

class BrandPermission
{
    public static function filterIndex($query)
    {
        $user = User::auth();

        if ($user && !$user->isAdmin()) {
            $query->where('availability', true);
        }

        return $query;
    }
} 