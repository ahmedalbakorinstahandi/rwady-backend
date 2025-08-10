<?php

namespace App\Console\Commands;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Media;
use App\Models\Product;
use App\Models\CategoryProduct;
use App\Models\Seo;
use App\Services\ImageService;
use App\Services\OrderHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use League\Csv\Reader;

class ImportUpdateBoolFieldsCategoriesCommand extends Command
{
    protected $signature = 'import:update-bool-fields-categories {file}';
    protected $description = 'Import categories from CSV file with logging progress';

    public function handle(): void
    {
        $csvPath = $this->argument('file');
        if (!file_exists($csvPath)) {
            $this->error("File not found: {$csvPath}");
            return;
        }

        $csv = Reader::createFromPath($csvPath, 'r');
        $csv->setHeaderOffset(0);

        $records = $csv->getRecords();

        $count = 0;
        foreach ($records as $row) {
            if (strtolower(trim($row['type'] ?? '')) !== 'category') {
                continue;
            }

            $path = $row['category_path'] ?? '';
            $parts = array_map('trim', explode('/', $path));
            $categoryName = end($parts);
            
            $category = Category::where('name->ar', $categoryName)->first();
            if (!$category) {
                continue;
            }

            $category->update([
                'availability' => $row['category_is_available'] == 'TRUE' ? true : false,
            ]);

            $count++;

            $this->info("Updated category {$categoryName} {$category->id} {$row['category_is_available']}");

            if ($count % 10 === 0) {
                $this->info("Updated {$count} categories so far...");
            }
        }

        $this->info('✅ Update completed. Total categories updated: ' . $count);
    }
}
