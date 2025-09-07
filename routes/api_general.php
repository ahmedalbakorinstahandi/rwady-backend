<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\GeneralController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SettingController;
use App\Services\ResponseService;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Group;

Route::prefix('general')->group(function () {
    Route::post('images/upload', [ImageController::class, 'uploadImage']);
    Route::post('files/upload', [ImageController::class, 'uploadFile']);
    Route::get('colors', [GeneralController::class, 'colors']);

    Route::get('/settings', [SettingController::class, 'index']);
    Route::get('/settings/{id}', [SettingController::class, 'show']);


    Route::prefix('addresses')->group(function () {
        Route::get('countries', [CountryController::class, 'index']);

        Route::get('cities', [CityController::class, 'index']);

        Route::get('areas', [AreaController::class, 'index']);
    });


    Route::middleware(['auth:sanctum'])->group(function () {
        Route::prefix('notifications')->controller(NotificationController::class)->group(function () {
            Route::get('/', 'index');
            Route::get('/unread-count', 'unreadCount');
            Route::post('/{id}/read',  'readNotification');
            Route::get('{id}', 'show');
        });
    });
});
