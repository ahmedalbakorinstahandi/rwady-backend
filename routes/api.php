<?php

use App\Http\Middleware\SetLocaleMiddleware;
use Illuminate\Support\Facades\Route;

Route::middleware(SetLocaleMiddleware::class)->group(function () {
    require_once __DIR__ . '/api_auth.php';
    require_once __DIR__ . '/api_user.php';
    require_once __DIR__ . '/api_admin.php';
    require_once __DIR__ . '/api_general.php';
});
