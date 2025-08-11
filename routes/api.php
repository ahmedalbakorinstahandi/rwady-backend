<?php

use App\Http\Controllers\Webhook\QiPaymentWebhookController;
use App\Http\Middleware\SetLocaleMiddleware;
use Illuminate\Support\Facades\Route;

Route::post('webhook/qi-payment', [QiPaymentWebhookController::class, 'handle']);


Route::middleware([SetLocaleMiddleware::class])->group(function () {
    require_once __DIR__ . '/api_auth.php';
    require_once __DIR__ . '/api_user.php';
    require_once __DIR__ . '/api_admin.php';
    require_once __DIR__ . '/api_general.php';
});

// Test routes for location service
Route::prefix('test')->group(function () {
    Route::get('/location', [App\Http\Controllers\TestLocationController::class, 'testLocation']);
    Route::post('/location/custom', [App\Http\Controllers\TestLocationController::class, 'testCustomLocation']);
    Route::get('/location/api-connection', [App\Http\Controllers\TestLocationController::class, 'testApiConnection']);
});
