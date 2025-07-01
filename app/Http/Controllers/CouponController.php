<?php

namespace App\Http\Controllers;

use App\Http\Requests\Coupon\CheckRequest;
use App\Http\Resources\CouponResource;
use App\Http\Services\CouponService;
use App\Services\ResponseService;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    protected $couponService;

    public function __construct(CouponService $couponService)
    {
        $this->couponService = $couponService;
    }

    public function checkCoupon(CheckRequest $request)
    {
        $coupon = $this->couponService->checkCoupon($request->validated()['coupon']);

        return ResponseService::response([
            'success' => true,
            'data' => $coupon,
            'resource' => CouponResource::class,
            'message' => 'messages.coupon.checked',
        ], 200);
    }
}
