<?php

use App\Http\Controllers\ImageController;
use App\Services\ResponseService;
use Illuminate\Support\Facades\Route;

Route::prefix('general')->group(function () {
    Route::post('images/upload', [ImageController::class, 'uploadImage']);
    Route::post('files/upload', [ImageController::class, 'uploadFile']);
    // colors
    Route::get('colors', function () {
        return ResponseService::response([
            'success' => true,
            'data' => [
                [
                    'name' => 'أسود',
                    'code' => '#000000'
                ],
                [
                    'name' => 'أبيض',
                    'code' => '#ffffff'
                ],
                [
                    'name' => 'أحمر',
                    'code' => '#ff0000'
                ],
                [
                    'name' => 'أزرق',
                    'code' => '#0000ff'
                ],
                [
                    'name' => 'أخضر',
                    'code' => '#00ff00'
                ],
                [
                    'name' => 'أصفر',
                    'code' => '#ffff00'
                ],
                [
                    'name' => 'برتقالي',
                    'code' => '#ffa500'
                ],
                [
                    'name' => 'بنفسجي',
                    'code' => '#800080'
                ],
                [
                    'name' => 'وردي',
                    'code' => '#ffc0cb'
                ],
                [
                    'name' => 'رمادي',
                    'code' => '#808080'
                ],
                [
                    'name' => 'أزرق مخضر',
                    'code' => '#008080'
                ],
                [
                    'name' => 'بني',
                    'code' => '#a52a2a'
                ],
                [
                    'name' => 'سماوي',
                    'code' => '#00ffff'
                ],
                [
                    'name' => 'فوشي',
                    'code' => '#ff00ff'
                ],
                [
                    'name' => 'أزرق فاتح',
                    'code' => '#add8e6'
                ],
                [
                    'name' => 'بيج',
                    'code' => '#f5f5dc'
                ],
                [
                    'name' => 'أخضر فاتح',
                    'code' => '#90ee90'
                ],
                [
                    'name' => 'رملي',
                    'code' => '#d2b48c'
                ],
                [
                    'name' => 'برتقالي غامق',
                    'code' => '#ff4500'
                ],
                [
                    'name' => 'كريمي',
                    'code' => '#f0e68c'
                ],
                [
                    'name' => 'أزرق فولاذي',
                    'code' => '#4682b4'
                ],
                [
                    'name' => 'ذهبي',
                    'code' => '#ffd700'
                ],
                [
                    'name' => 'رمادي فاتح',
                    'code' => '#d3d3d3'
                ],
                [
                    'name' => 'رمادي داكن',
                    'code' => '#2f4f4f'
                ],
                [
                    'name' => 'أحمر ناري',
                    'code' => '#b22222'
                ],
                [
                    'name' => 'نيلي',
                    'code' => '#4b0082'
                ],
                [
                    'name' => 'رمادي متوسط',
                    'code' => '#a9a9a9'
                ],
                [
                    'name' => 'أصفر فاتح',
                    'code' => '#eee8aa'
                ],
                [
                    'name' => 'أزرق باهت',
                    'code' => '#b0e0e6'
                ],
                [
                    'name' => 'وردي باهت',
                    'code' => '#f08080'
                ],
                [
                    'name' => 'تركواز',
                    'code' => '#7fffd4'
                ],
                [
                    'name' => 'تركواز داكن',
                    'code' => '#20b2aa'
                ],
                [
                    'name' => 'زيتي فاتح',
                    'code' => '#8fbc8f'
                ],
                [
                    'name' => 'ذهبي داكن',
                    'code' => '#daa520'
                ],
                [
                    'name' => 'لافندر',
                    'code' => '#e6e6fa'
                ],
                [
                    'name' => 'رمادي أزرق',
                    'code' => '#b0c4de'
                ]
            ]
        ]);
    });
});
