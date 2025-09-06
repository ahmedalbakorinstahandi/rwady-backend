<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::get('/me', [AuthController::class, 'getMyData']);
    Route::post('/delete-account/request', [AuthController::class, 'requestDeleteAccount'])->middleware('auth:sanctum');
    Route::post('/delete-account/confirm', [AuthController::class, 'confirmDeleteAccount'])->middleware('auth:sanctum');
});
