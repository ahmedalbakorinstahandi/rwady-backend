<?php

namespace App\Http\Permissions;

class CategoryPermission
{
    public static function filterIndex($query)
    {
        $query->where('availability', true);
        return $query;
    }
} 