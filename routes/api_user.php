<?php

// prfix user 

use App\Http\Controllers\AddressController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CartItemController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\FeaturedSectionController;
use App\Http\Controllers\HomeSectionController;
use App\Http\Controllers\InstallmentController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderPaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('user')->group(function () {

    Route::get('/home-sections', [HomeSectionController::class, 'index']);


    Route::prefix('products')->group(function () {
        Route::get('/', [ProductController::class, 'index']);
        Route::get('/{product}', [ProductController::class, 'show']);
        Route::post('/{product}/toggle-favorite', [ProductController::class, 'toggleFavorite']);
    });

    Route::prefix('categories')->group(function () {
        Route::get('/', [CategoryController::class, 'index']);
        Route::get('/{category}', [CategoryController::class, 'show']);
    });

    Route::prefix('brands')->group(function () {
        Route::get('/', [BrandController::class, 'index']);
        Route::get('/{brand}', [BrandController::class, 'show']);
    });

    Route::prefix('banners')->group(function () {
        Route::get('/', [BannerController::class, 'index']);
        Route::get('/{banner}', [BannerController::class, 'show']);
    });

    Route::prefix('featured-sections')->group(function () {
        Route::get('/', [FeaturedSectionController::class, 'index']);
        Route::get('/{featuredSection}', [FeaturedSectionController::class, 'show']);
    });

    Route::middleware('auth:sanctum')->group(function () {

        Route::get('/me', [UserController::class, 'getMyData']);
        Route::put('/me', [UserController::class, 'updateMyData']);

        Route::prefix('addresses')->group(function () {
            Route::get('/', [AddressController::class, 'index']);
            Route::get('/{id}', [AddressController::class, 'show']);
            Route::post('/', [AddressController::class, 'create']);
            Route::put('/{id}', [AddressController::class, 'update']);
            Route::delete('/{id}', [AddressController::class, 'delete']);
        });

        Route::prefix('cart-items')->group(function () {
            Route::get('/', [CartItemController::class, 'index']);
            Route::get('/{id}', [CartItemController::class, 'show']);
            Route::post('/', [CartItemController::class, 'create']);
            Route::put('/{id}', [CartItemController::class, 'update']);
            Route::delete('/{id}', [CartItemController::class, 'delete']);
        });
    });

    Route::prefix('coupons')->group(function () {
        Route::post('/check', [CouponController::class, 'checkCoupon']);
    });

    Route::prefix('orders')->group(function () {
        Route::get('/', [OrderController::class, 'index']);
        Route::get('/{id}', [OrderController::class, 'show']);
        Route::post('/', [OrderController::class, 'create']);
        Route::put('/{id}', [OrderController::class, 'update']);
        Route::delete('/{id}', [OrderController::class, 'delete']);
        Route::post('/check-details', [OrderController::class, 'checkOrderDetails']);
        Route::post('/{id}/confirm-otp', [OrderController::class, 'confirmOtp']);
    });

    Route::prefix('installments')->group(function () {
        Route::post('/check-eligibility', [InstallmentController::class, 'checkEligibility']);
        Route::post('/validate-plan', [InstallmentController::class, 'validatePlan']);
        Route::post('/confirm', [InstallmentController::class, 'confirmInstallment']);
    });

    Route::prefix('order-payments')->group(function () {
        Route::get('/', [OrderPaymentController::class, 'index']);
    });


});