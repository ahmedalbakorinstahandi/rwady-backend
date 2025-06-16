<?php

namespace App\Http\Permissions;

use App\Models\User;

class ProductPermission
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
