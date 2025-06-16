<?php

namespace App\Http\Permissions;

class ProductPermission
{
    public static function filterIndex($query)
    {
        $query->where('is_active', true);

        return $query;
    }
}
