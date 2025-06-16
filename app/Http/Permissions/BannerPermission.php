<?php

namespace App\Http\Permissions;

class BannerPermission
{
    public static function filterIndex($query)
    {
        return $query;
    }
} 