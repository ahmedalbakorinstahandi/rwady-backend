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
         ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
