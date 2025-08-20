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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use League\Csv\Reader;

class ImportProductsCommand extends Command
{
    protected $signature = 'import:products';
    protected $description = 'Import products from CSV file with logging progress';

    public function handle(): void
    {
        // file storage/files/catalog_2025-08-04_16-00.csv
        $csvPath = "storage/files/catalog_2025-08-04_16-00.csv";
        if (!file_exists($csvPath)) {
            Log::error("File not found: {$csvPath}");
            return;
        }

        Log::info("Importing products from {$csvPath}");

        $csv = Reader::createFromPath($csvPath, 'r');
        $csv->setHeaderOffset(0);
        $records = $csv->getRecords();

        $count = 0;
        foreach ($records as $row) {
            if (strtolower(trim($row['type'] ?? '')) !== 'product') {
                continue;
            }

            $sku = trim($row['product_sku']);
            if (!$sku || Product::where('sku', $sku)->exists()) {
                continue;
            }

            Log::info("Processing product SKU: {$sku}");

            $categoryIds = collect();

             for ($i = 1; $i <= 8; $i++) {
                $rawValue = trim($row["product_category_{$i}"] ?? '');
            
                if (!$rawValue) {
                    continue;
                }
            
                 $parts = array_map('trim', explode('/', $rawValue));
            
                 foreach ($parts as $part) {
                    if ($part) {
                        $category = Category::where('name->ar', $part)->first();
                        if ($category) {
                            $categoryIds->push($category->id);
                        }
                    }
                }
            }

            $categoryIds = $categoryIds->unique()->values()->toArray();
            

            $brandId = null;
            $brandName = trim($row['product_brand'] ?? '');
            if ($brandName) {
                $brand = Brand::firstOrCreate(
                    ['name->ar' => $brandName],
                    ['name' => ['ar' => $brandName, 'en' => $brandName], 'image' => '']
                );
                $brandId = $brand->id;
            }

            $product = Product::create([
                'sku' => $sku,
                'name' => ['ar' => trim($row['product_name']), 'en' => trim($row['product_name'])],
                'description' => ['ar' => trim($row['product_description']), 'en' => trim($row['product_description'])],
                'price' => (float) $row['product_price'],
                'price_after_discount' => (float) $row['product_compare_to_price'] ?: null,
                'cost_price' => (float) $row['product_cost_price'],
                'stock_unlimited' =>  $row['product_is_inventory_tracked'] == 'true' ? true : false,
                'stock' => (int) $row['product_quantity'],
                'out_of_stock' => $row['product_quantity_out_of_stock_behaviour'] == 'SHOW' ? 'show_on_storefront' : ($row['product_quantity_out_of_stock_behaviour'] == 'ALLOW_PREORDER' ? 'show_and_allow_pre_order' : 'hide_from_storefront'),
                'minimum_purchase' => $row['product_quantity_minimum_allowed_for_purchase'] ?: null,
                'maximum_purchase' => $row['product_quantity_maximum_allowed_for_purchase'] ?: null,
                'availability' => $row['product_is_available'] == 'true' ? true : false,
                'requires_shipping' => $row['product_is_shipping_required'] == 'true' ? true : false,
                'shipping_type' => $row['product_shipping_type'] == 'GLOBAL_METHODS' ? 'default' : 'free_shipping',
                'shipping_rate_single' => (float) $row['product_shipping_fixed_rate'],
                'weight' => (float) $row['product_weight'],
                'length' => (float) $row['product_length'],
                'width' => (float) $row['product_width'],
                'height' => (float) $row['product_height'],
                'related_category_limit' => (int) $row['product_related_items_random_number_of_items'],
                'is_recommended' => $row['product_is_featured'] == 'true' ? true : false,
                'ribbon_text' => ['ar' => $row['product_ribbon_text'], 'en' => $row['product_ribbon_text']],
                'ribbon_color' => $row['product_ribbon_color'],
            ]);

            OrderHelper::assign($product, 'orders');

            foreach ($categoryIds as $categoryId) {
                CategoryProduct::create(['category_id' => $categoryId, 'product_id' => $product->id]);
            }

            if ($brandId) {
                $product->brands()->attach([$brandId]);
            }

            if (!empty($row['product_seo_title']) && !empty($row['product_seo_description'])) {
                $product->seo()->updateOrCreate(
                    ['seoable_type' => Product::class, 'seoable_id' => $product->id],
                    ['meta_title' => $row['product_seo_title'], 'meta_description' => $row['product_seo_description'], 'keywords' => null, 'image' => null]
                );
            }

            if (!empty($row['product_media_main_image_url'])) {
                $this->downloadAndAttachImage($product, $row['product_media_main_image_url']);
            }

            foreach ($row as $key => $value) {
                if (Str::startsWith($key, 'product_media_gallery_image_url_') && $value) {
                    $this->downloadAndAttachImage($product, $value);
                }
            }

            $count++;
            if ($count % 10 === 0) {
                Log::info("Imported {$count} products so far...");
            }
        }

        Log::info("✅ Import completed. Total products imported: {$count}");
    }

    private function downloadAndAttachImage($product, $url)
    {
        try {
            $tempFile = tempnam(sys_get_temp_dir(), 'img_');
            file_put_contents($tempFile, file_get_contents($url));
            
            // استخراج اسم الصورة من الرابط
            $imageName = $this->extractImageNameFromUrl($url);
            
            $imagePath = ImageService::storeImageWithOriginalName($tempFile, 'products', $imageName);
            unlink($tempFile);

            $media = Media::create([
                'product_id' => $product->id,
                'path' => $imagePath,
                'type' => 'image',
                'source' => 'file',
                'orders' => 1,
            ]);

            OrderHelper::assign($media, 'orders');
        } catch (\Exception $e) {
            Log::error("Failed to download image for product {$product->sku}: {$e->getMessage()}");
        }
    }

    /**
     * استخراج اسم الصورة من الرابط
     */
    private function extractImageNameFromUrl($url)
    {
        // استخراج اسم الملف من الرابط
        $parsedUrl = parse_url($url);
        $path = $parsedUrl['path'] ?? '';
        
        // استخراج اسم الملف مع اللاحقة
        $fileName = basename($path);
        
        // إزالة اللاحقة (.png, .jpg, .jpeg, .webp, إلخ)
        $nameWithoutExtension = pathinfo($fileName, PATHINFO_FILENAME);
        
        // تنظيف الاسم من الأحرف الخاصة
        $cleanName = preg_replace('/[^a-zA-Z0-9\-\_\s]/', '_', $nameWithoutExtension);
        $cleanName = preg_replace('/\s+/', '_', $cleanName); // استبدال المسافات بـ _
        $cleanName = trim($cleanName, '._-');
        
        // إذا كان الاسم فارغاً، استخدم اسم افتراضي
        if (empty($cleanName)) {
            $cleanName = 'product_image';
        }
        
        return $cleanName;
    }
}
