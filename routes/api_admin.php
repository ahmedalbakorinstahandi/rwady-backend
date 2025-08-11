<?php

use App\Http\Controllers\BannerController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\FeaturedSectionController;
use App\Http\Controllers\HomeSectionController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->middleware(['auth:sanctum', AdminMiddleware::class])->group(function () {


    // dashboard analytics
    Route::get('/dashboard/analytics', [DashboardController::class, 'analytics']);


    Route::prefix('home-sections')->group(function () {
        Route::get('/', [HomeSectionController::class, 'index']);
        Route::get('/{id}', [HomeSectionController::class, 'show']);
        Route::post('/', [HomeSectionController::class, 'create']);
        Route::put('/{id}', [HomeSectionController::class, 'update']);
        Route::delete('/{id}', [HomeSectionController::class, 'delete']);
        Route::put('/{id}/reorder', [HomeSectionController::class, 'reorder']);
    });

    Route::prefix('products')->group(function () {
        Route::get('/', [ProductController::class, 'index']);
        Route::get('/{id}', [ProductController::class, 'show']);
        Route::post('/', [ProductController::class, 'create']);
        Route::put('/{id}', [ProductController::class, 'update']);
        Route::delete('/{id}', [ProductController::class, 'delete']);
        Route::put('/{id}/reorder', [ProductController::class, 'reorder']);
        Route::put('/{id}/media/{mediaId}/reorder', [ProductController::class, 'reorderMedia']);
    });

    Route::prefix('categories')->group(function () {
        Route::get('/', [CategoryController::class, 'index']);
        Route::get('/{id}', [CategoryController::class, 'show']);
        Route::post('/', [CategoryController::class, 'create']);
        Route::put('/{id}', [CategoryController::class, 'update']);
        Route::delete('/{id}', [CategoryController::class, 'delete']);
        Route::put('/{id}/reorder', [CategoryController::class, 'reorder']);
        Route::post('/{id}/assign-products', [CategoryController::class, 'assignProductsToCategory']);
        Route::post('/{id}/unassign-products', [CategoryController::class, 'unassignProductsFromCategory']);
    });

    Route::prefix('brands')->group(function () {
        Route::get('/', [BrandController::class, 'index']);
        Route::get('/{id}', [BrandController::class, 'show']);
        Route::post('/', [BrandController::class, 'create']);
        Route::put('/{id}', [BrandController::class, 'update']);
        Route::delete('/{id}', [BrandController::class, 'delete']);
    });

    Route::prefix('banners')->group(function () {
        Route::get('/', [BannerController::class, 'index']);
        Route::get('/{id}', [BannerController::class, 'show']);
        Route::post('/', [BannerController::class, 'create']);
        Route::put('/{id}', [BannerController::class, 'update']);
        Route::delete('/{id}', [BannerController::class, 'delete']);
    });

    Route::prefix('featured-sections')->group(function () {
        Route::get('/', [FeaturedSectionController::class, 'index']);
        Route::get('/{id}', [FeaturedSectionController::class, 'show']);
        Route::post('/', [FeaturedSectionController::class, 'create']);
        Route::put('/{id}', [FeaturedSectionController::class, 'update']);
        Route::delete('/{id}', [FeaturedSectionController::class, 'delete']);
    });

    Route::prefix('orders')->group(function () {
        Route::get('/', [OrderController::class, 'index']);
        Route::get('/{id}', [OrderController::class, 'show']);
        Route::put('/{id}', [OrderController::class, 'update']);
        // Route::delete('/{id}', [OrderController::class, 'delete']);
    });

    Route::prefix('settings')->group(function () {
        Route::get('/', [SettingController::class, 'index']);
        Route::get('/{idOrKey}', [SettingController::class, 'show']);
        Route::post('/', [SettingController::class, 'create']);
        Route::put('/', [SettingController::class, 'updateMany']);
        Route::put('/{idOrKey}', [SettingController::class, 'updateOne']);
        Route::delete('/{idOrKey}', [SettingController::class, 'delete']);
    });

    Route::prefix('coupons')->group(function () {
        Route::get('/', [CouponController::class, 'index']);
        Route::get('/{id}', [CouponController::class, 'show']);
        Route::post('/', [CouponController::class, 'create']);
        Route::put('/{id}', [CouponController::class, 'update']);
        Route::delete('/{id}', [CouponController::class, 'delete']);
    });

    // Promotion
    Route::prefix('promotions')->group(function () {
        Route::get('/', [PromotionController::class, 'index']);
        Route::get('/{id}', [PromotionController::class, 'show']);
        Route::post('/', [PromotionController::class, 'create']);
        Route::put('/{id}', [PromotionController::class, 'update']);
        Route::delete('/{id}', [PromotionController::class, 'delete']);
    });

    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::get('/{id}', [UserController::class, 'show']);
        Route::post('/', [UserController::class, 'create']);
        Route::put('/{id}', [UserController::class, 'update']);
        Route::delete('/{id}', [UserController::class, 'delete']);
    });
});
