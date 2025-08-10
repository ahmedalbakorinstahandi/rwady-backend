<?php

namespace App\Console\Commands;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Media;
use App\Models\Product;
use App\Models\CategoryProduct;
use App\Services\ImageService;
use App\Services\OrderHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use League\Csv\Reader;

class ImportUpdateBoolFieldsProductsCommand extends Command
{
    protected $signature = 'import:update-bool-fields-products {file}';
    protected $description = 'Import products from CSV file with logging progress';

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
            if (strtolower(trim($row['type'] ?? '')) !== 'product') {
                continue;
            }

            $sku = trim($row['product_sku']);
            $product = Product::where('sku', $sku)->first();
            if (!$product) {
                continue;
            }

            $product->update([
                'stock_unlimited' =>  $row['product_is_inventory_tracked'] != 'TRUE' ? true : false,
                'availability' => $row['product_is_available'] == 'TRUE' ? true : false,
                'requires_shipping' => $row['product_is_shipping_required'] == 'TRUE' ? true : false,
                'is_recommended' => $row['product_is_featured'] == 'TRUE' ? true : false,
            ]);



            $count++;

            $this->info("Updated product {$sku}");

            if ($count % 10 === 0) {
                $this->info("Updated {$count} products so far...");
            }
        }

        $this->info("✅ Update completed. Total products updated: {$count}");
    }
}
