<?php

namespace App\Http\Permissions;

use App\Models\Coupon;
use App\Services\MessageService;

class CouponPermission 
{
    public static function create($data)
    {
        $coupon = Coupon::where('code', $data['code'])->first();

        if ($coupon) {
            return MessageService::abort(400, 'messages.coupon.already_exists');
        }

        return $data;
    }
}
