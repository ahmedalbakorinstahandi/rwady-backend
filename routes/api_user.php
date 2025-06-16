<?php

// prfix user 

use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::prefix('user')->group(function () {

    // sanctum auth
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/products', [ProductController::class, 'index']);
    });
});
