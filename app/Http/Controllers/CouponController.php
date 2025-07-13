<?php

namespace App\Http\Controllers;

use App\Http\Permissions\CouponPermission;
use App\Http\Requests\Coupon\CheckRequest;
use App\Http\Requests\Coupon\CreateCouponRequest;
use App\Http\Requests\Coupon\UpdateCouponRequest;
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

    public function index(Request $request)
    {
        $coupons = $this->couponService->index($request->all());

        return ResponseService::response([
            'success' => true,
            'data' => $coupons,
            'resource' => CouponResource::class,
            'meta' => true,
        ], 200);
    }

    public function show($id)
    {
        $coupon = $this->couponService->show($id);

        return ResponseService::response([
            'success' => true,
            'data' => $coupon,
            'resource' => CouponResource::class,
        ], 200);
    }

    public function create(CreateCouponRequest $request)
    {
        $data = CouponPermission::create($request->validated());

        $coupon = $this->couponService->create($data);

        return ResponseService::response([
            'success' => true,
            'data' => $coupon,
            'resource' => CouponResource::class,
            'message' => 'messages.coupon.created',
        ], 200);
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

    public function update(UpdateCouponRequest $request, $id)
    {
        $coupon = $this->couponService->update($id, $request->validated());

        return ResponseService::response([
            'success' => true,
            'data' => $coupon,
            'resource' => CouponResource::class,
            'message' => 'messages.coupon.updated',
        ], 200);
    }

    public function delete($id)
    {
        $coupon = $this->couponService->show($id);
        
        $coupon = $this->couponService->delete($coupon);

        return ResponseService::response([
            'success' => true,
            'data' => $coupon,
            'resource' => CouponResource::class,
            'message' => 'messages.coupon.deleted',
        ], 200);
    }
}
