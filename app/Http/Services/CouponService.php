<?php

namespace App\Http\Services;

use App\Models\Coupon;
use App\Services\MessageService;

class CouponService
{
    public function checkCoupon($coupon)
    {
        $coupon = Coupon::where('code', $coupon)->first();
        if (!$coupon || !$coupon->is_active) {
            return MessageService::abort(404, 'messages.coupon.not_found');
        }

        return $coupon;
    }
}
