<?php

namespace App\Http\Permissions;

class ProductPermission
{
    public static function filterIndex($query)
    {
        $query->where('availability‍', true);

        return $query;
    }
}
