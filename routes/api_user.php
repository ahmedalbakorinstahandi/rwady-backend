<?php

// prfix user 

use App\Http\Controllers\HomeSectionController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('user')->group(function () {

    Route::get('/home-sections', [HomeSectionController::class, 'index']);
    Route::get('/products', [ProductController::class, 'index']);

    Route::middleware('auth:sanctum')->group(function () {

        Route::get('/me', [UserController::class, 'getMyData']);
        Route::put('/me', [UserController::class, 'updateMyData']);
    });
});
