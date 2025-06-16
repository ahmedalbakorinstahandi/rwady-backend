<?php

namespace App\Http\Permissions;

class FeaturedSectionPermission
{
    public static function filterIndex($query)
    {
        return $query;
    }
} 