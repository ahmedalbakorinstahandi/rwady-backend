<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::get('/me', [AuthController::class, 'getMyData']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
});
