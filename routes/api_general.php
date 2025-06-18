<?php

use App\Http\Controllers\GeneralController;
use App\Http\Controllers\ImageController;
use App\Services\ResponseService;
use Illuminate\Support\Facades\Route;

Route::prefix('general')->group(function () {
    Route::post('images/upload', [ImageController::class, 'uploadImage']);
    Route::post('files/upload', [ImageController::class, 'uploadFile']);
    Route::get('colors', [GeneralController::class, 'colors']);
});
