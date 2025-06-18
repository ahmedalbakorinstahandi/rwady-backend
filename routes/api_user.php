<?php

// prfix user 

use App\Http\Controllers\BannerController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\FeaturedSectionController;
use App\Http\Controllers\HomeSectionController;
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
    });
});
