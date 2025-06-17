<?php

use App\Http\Controllers\BannerController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\FeaturedSectionController;
use App\Http\Controllers\HomeSectionController;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->middleware(['auth:sanctum', AdminMiddleware::class])->group(function () {


    Route::prefix('home-sections')->group(function () {
        Route::get('/', [HomeSectionController::class, 'index']);
        Route::get('/{id}', [HomeSectionController::class, 'show']);
        Route::post('/', [HomeSectionController::class, 'create']);
        // Route::put('/{id}', [HomeSectionController::class, 'update']);
        // Route::delete('/{id}', [HomeSectionController::class, 'delete']);
        Route::put('/reorder/{id}', [HomeSectionController::class, 'reorder']);
    });

    Route::prefix('products')->group(function () {
        Route::get('/', [ProductController::class, 'index']);
        Route::get('/{id}', [ProductController::class, 'show']);
        Route::post('/', [ProductController::class, 'create']);
        Route::put('/{id}', [ProductController::class, 'update']);
        Route::delete('/{id}', [ProductController::class, 'delete']);
    });

    Route::prefix('categories')->group(function () {
        Route::get('/', [CategoryController::class, 'index']);
        Route::get('/{id}', [CategoryController::class, 'show']);
        Route::post('/', [CategoryController::class, 'create']);
        Route::put('/{id}', [CategoryController::class, 'update']);
        Route::delete('/{id}', [CategoryController::class, 'delete']);
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
});
