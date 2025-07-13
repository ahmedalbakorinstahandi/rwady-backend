<?php

namespace App\Http\Services;

use App\Models\Coupon;
use App\Services\FilterService;
use App\Services\MessageService;

class CouponService
{

    public function index($data)
    {
        $query = Coupon::query();

        $coupons = FilterService::applyFilters(
            $query,
            $data,
            ['code'],
            ['amount'],
            ['start_date', 'end_date'],
            ['code', 'type', 'amount', 'is_active',],
            ['type']
        );

        return $coupons;
    }

    public function show($id)
    {
        $coupon = Coupon::where('id', $id)->first();
        if (!$coupon) {
            return MessageService::abort(404, 'messages.coupon.not_found');
        }

        return $coupon;
    }

    public function checkCoupon($coupon)
    {
        $coupon = Coupon::where('code', $coupon)->first();
        if (!$coupon || !$coupon->is_active) {
            return MessageService::abort(404, 'messages.coupon.invalid');
        }

        return $coupon;
    }

    public function create($data)
    {
        $coupon = Coupon::create($data);

        return $coupon;
    }

    public function update($coupon, $data)
    {
        $coupon->update($data);

        return $coupon;
    }

    public function delete($coupon)
    {
        $coupon->delete();

        return $coupon;
    }
}
