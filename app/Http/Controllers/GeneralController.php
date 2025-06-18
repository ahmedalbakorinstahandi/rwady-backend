<?php

namespace App\Http\Controllers;

use App\Services\ResponseService;
use Illuminate\Http\Request;

class GeneralController extends Controller
{
    public function colors()
    {
        return ResponseService::response([
            'success' => true,
            'data' => [
                [
                    'name' => ['ar' => 'أسود', 'en' => 'Black'],
                    'code' => '#000000'
                ],
                [
                    'name' => ['ar' => 'أبيض', 'en' => 'White'],
                    'code' => '#ffffff'
                ],
                [
                    'name' => ['ar' => 'أحمر', 'en' => 'Red'],
                    'code' => '#ff0000'
                ],
                [
                    'name' => ['ar' => 'أزرق', 'en' => 'Blue'],
                    'code' => '#0000ff'
                ],
                [
                    'name' => ['ar' => 'أخضر', 'en' => 'Green'],
                    'code' => '#00ff00'
                ],
                [
                    'name' => ['ar' => 'أصفر', 'en' => 'Yellow'],
                    'code' => '#ffff00'
                ],
                [
                    'name' => ['ar' => 'برتقالي', 'en' => 'Orange'],
                    'code' => '#ffa500'
                ],
                [
                    'name' => ['ar' => 'بنفسجي', 'en' => 'Purple'],
                    'code' => '#800080'
                ],
                [
                    'name' => ['ar' => 'وردي', 'en' => 'Pink'],
                    'code' => '#ffc0cb'
                ],
                [
                    'name' => ['ar' => 'رمادي', 'en' => 'Gray'],
                    'code' => '#808080'
                ],
                [
                    'name' => ['ar' => 'أزرق مخضر', 'en' => 'Blue-Green'],
                    'code' => '#008080'
                ],
                [
                    'name' => ['ar' => 'بني', 'en' => 'Brown'],
                    'code' => '#a52a2a'
                ],
                [
                    'name' => ['ar' => 'سماوي', 'en' => 'Sky Blue'],
                    'code' => '#00ffff'
                ],
                [
                    'name' => ['ar' => 'فوشي', 'en' => 'Fuchsia'],
                    'code' => '#ff00ff'
                ],
                [
                    'name' => ['ar' => 'أزرق فاتح', 'en' => 'Light Blue'],
                    'code' => '#add8e6'
                ],
                [
                    'name' => ['ar' => 'بيج', 'en' => 'Beige'],
                    'code' => '#f5f5dc'
                ],
                [
                    'name' => ['ar' => 'أخضر فاتح', 'en' => 'Light Green'],
                    'code' => '#90ee90'
                ],
                [
                    'name' => ['ar' => 'رملي', 'en' => 'Sand'],
                    'code' => '#d2b48c'
                ],
                [
                    'name' => ['ar' => 'برتقالي غامق', 'en' => 'Dark Orange'],
                    'code' => '#ff4500'
                ],
                [
                    'name' => ['ar' => 'كريمي', 'en' => 'Cream'],
                    'code' => '#f0e68c'
                ],
                [
                    'name' => ['ar' => 'أزرق فولاذي', 'en' => 'Steel Blue'],
                    'code' => '#4682b4'
                ],
                [
                    'name' => ['ar' => 'ذهبي', 'en' => 'Gold'],
                    'code' => '#ffd700'
                ],
                [
                    'name' => ['ar' => 'رمادي فاتح', 'en' => 'Light Gray'],
                    'code' => '#d3d3d3'
                ],
                [
                    'name' => ['ar' => 'رمادي داكن', 'en' => 'Dark Gray'],
                    'code' => '#2f4f4f'
                ],
                [
                    'name' => ['ar' => 'أحمر ناري', 'en' => 'Fire Red'],
                    'code' => '#b22222'
                ],
                [
                    'name' => ['ar' => 'نيلي', 'en' => 'Navy Blue'],
                    'code' => '#4b0082'
                ],
                [
                    'name' => ['ar' => 'رمادي متوسط', 'en' => 'Medium Gray'],
                    'code' => '#a9a9a9'
                ],
                [
                    'name' => ['ar' => 'أصفر فاتح', 'en' => 'Light Yellow'],
                    'code' => '#eee8aa'
                ],
                [
                    'name' => ['ar' => 'أزرق باهت', 'en' => 'Pale Blue'],
                    'code' => '#b0e0e6'
                ],
                [
                    'name' => ['ar' => 'وردي باهت', 'en' => 'Pale Pink'],
                    'code' => '#f08080'
                ],
                [
                    'name' => ['ar' => 'تركواز', 'en' => 'Turquoise'],
                    'code' => '#7fffd4'
                ],
                [
                    'name' => ['ar' => 'تركواز داكن', 'en' => 'Dark Turquoise'],
                    'code' => '#20b2aa'
                ],
                [
                    'name' => ['ar' => 'زيتي فاتح', 'en' => 'Light Olive'],
                    'code' => '#8fbc8f'
                ],
                [
                    'name' => ['ar' => 'ذهبي داكن', 'en' => 'Dark Gold'],
                    'code' => '#daa520'
                ],
                [
                    'name' => ['ar' => 'لافندر', 'en' => 'Lavender'],
                    'code' => '#e6e6fa'
                ],
                [
                    'name' => ['ar' => 'رمادي أزرق', 'en' => 'Blue Gray'],
                    'code' => '#b0c4de'
                ]
            ]
        ]);
    }
}
