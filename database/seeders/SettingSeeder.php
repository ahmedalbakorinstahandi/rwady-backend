<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'key' => 'video_url',
                'value' => 'https://www.youtube.com/watch?v=C6Rej-HdKsg',
                'type' => 'text',
                'allow_null' => false,
                'is_setting' => true,
            ],
            [
                'key' => 'cover_image_url_for_home_page_video',
                'value' => 'https://cdn.prod.website-files.com/64022de562115a8189fe542a/6616718fe4a871d7278a2037_Product-Concept-What-Is-It-And-How-Can-You-Best-Use-It.jpg',
                'type' => 'text',
                'allow_null' => false,
                'is_setting' => true,
            ],
            [
                'key' => 'default_shipping_rate_single',
                'value' => 10000,
                'type' => 'float',
                'allow_null' => false,
                'is_setting' => true,
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
