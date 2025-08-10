<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class AvatarService
{
    /**
     * توليد صورة رمزية بسيطة مع تخزين مؤقت
     */
    public static function generateAvatar($text, $size = 256, $background = 'random', $length = 1)
    {
        $cacheKey = "avatar_" . md5($text . $size . $background . $length);
        
        // التحقق من التخزين المؤقت
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }
        
        // إنشاء صورة بسيطة
        $image = self::createSimpleAvatar($text, $size, $background, $length);
        
        // حفظ في التخزين المؤقت لمدة ساعة
        Cache::put($cacheKey, $image, 3600);
        
        return $image;
    }
    
    /**
     * إنشاء صورة بسيطة
     */
    private static function createSimpleAvatar($text, $size, $background, $length)
    {
        // أخذ الحرف الأول من النص
        $firstChar = mb_substr($text, 0, $length);
        
        // ألوان خلفية عشوائية بسيطة
        $colors = ['#f44336', '#e91e63', '#9c27b0', '#673ab7', '#3f51b5', '#2196f3', '#03a9f4', '#00bcd4', '#009688', '#4caf50', '#8bc34a', '#cddc39', '#ffeb3b', '#ffc107', '#ff9800', '#ff5722'];
        
        if ($background === 'random') {
            $background = $colors[array_rand($colors)];
        }
        
        // إنشاء SVG بسيط
        $svg = '<svg width="' . $size . '" height="' . $size . '" xmlns="http://www.w3.org/2000/svg">';
        $svg .= '<rect width="100%" height="100%" fill="' . $background . '"/>';
        $svg .= '<text x="50%" y="50%" font-family="Arial, sans-serif" font-size="' . ($size * 0.4) . '" fill="white" text-anchor="middle" dy=".3em">' . htmlspecialchars($firstChar) . '</text>';
        $svg .= '</svg>';
        
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }
}
