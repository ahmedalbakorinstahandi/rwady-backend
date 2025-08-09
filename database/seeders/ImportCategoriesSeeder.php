<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Seo;
use App\Services\ImageService;
use App\Services\OrderHelper;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use League\Csv\Reader;
use League\Csv\Statement;

class ImportCategoriesSeeder extends Seeder
{
    public function run(): void
    {
        $csvPath =  storage_path('files/catalog_2025-08-04_16-00.csv');
        if (!file_exists($csvPath)) {
            $this->command->error("File not found: {$csvPath}");
            return;
        }
        
        $csv = Reader::createFromPath($csvPath, 'r');
        $csv->setHeaderOffset(0);

        $records = $csv->getRecords();

        // تخزين مؤقت لكل المسارات حسب المستوى
        $pathsByLevel = [];
        foreach ($records as $row) {
            if (strtolower(trim($row['type'] ?? '')) !== 'category') {
                continue;
            }
            $path = $row['category_path'] ?? null;
            if (!$path) continue;

            $parts = array_map('trim', explode('/', $path));
            $level = count($parts);
            $pathsByLevel[$level][] = [
                'names' => $parts,
                'row' => $row,
            ];
        }

        ksort($pathsByLevel); // تأكد من البدء بالجذر أولاً

        foreach ($pathsByLevel as $level => $entries) {
            foreach ($entries as $entry) {
                $names = $entry['names'];
                $row = $entry['row'];

                $parentId = null;
                $currentPath = [];


        

                foreach ($names as $name) {
                    $currentPath[] = $name;
                    $existing = Category::where('name->ar', $name)
                        ->where('parent_id', $parentId)
                        ->first();

                    if (!$existing) {
                        $category = new Category();
                        $category->name = ['ar' => $name, 'en' => $name];
                        $category->parent_id = $parentId;
                        $category->availability = (bool) ($row['category_is_available'] ?? true);
                        $category->description = ['ar' => $row['category_description'] ?? '', 'en' => $row['category_description'] ?? ''];
                        $category->order_by = is_numeric($row['category_order_by'] ?? '') ? (int) $row['category_order_by'] : 0;

                        if (!empty($row['category_image'])) {
                            try {
                                $tempFile = tempnam(sys_get_temp_dir(), 'img_');
                                file_put_contents($tempFile, file_get_contents($row['category_image']));
                                
                                $imagePath = ImageService::storeImage($tempFile, 'categories');
                                $category->image = $imagePath;
                                
                                unlink($tempFile);
                            } catch (\Throwable $e) {
                                echo "\nFailed to download image for {$name}: {$e->getMessage()}\n";
                            }
                        }

                        $category->save();

                        OrderHelper::assign($category);

                        // علاقة SEO
                        if ($row['category_seo_title'] || $row['category_seo_description']) {
                            Seo::updateOrCreate(
                                [
                                    'seoable_type' => Category::class,
                                    'seoable_id' => $category->id,
                                ],
                                [
                                    'meta_title' => $row['category_seo_title'] ?? '',
                                    'meta_description' => $row['category_seo_description'] ?? '',
                                    'keywords' => null,
                                    'image' => null,
                                ]
                            );
                        }

                        $parentId = $category->id;
                    } else {
                        $parentId = $existing->id;
                    }
                }
            }
        }

        $this->command->info('✅ Smart hierarchical category import completed.');
    }
}
