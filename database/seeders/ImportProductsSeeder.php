<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Media;
use App\Models\Product;
use App\Models\CategoryProduct;
use App\Services\ImageService;
use App\Services\OrderHelper;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use League\Csv\Reader;
use Illuminate\Support\Facades\DB;

class ImportProductsSeeder extends Seeder
{
    public function run(): void
    {
        $csvPath = storage_path('app/files/catalog_2025-08-04_16-00.csv');
        if (!file_exists($csvPath)) {
            $this->command->error("File not found: {$csvPath}");
            return;
        }

        $csv = Reader::createFromPath($csvPath, 'r');
        $csv->setHeaderOffset(0);

        $records = $csv->getRecords();

        

        DB::transaction(function () use ($records) {
            
            foreach ($records as $row) {

                if (strtolower(trim($row['type'] ?? '')) !== 'product') {
                    continue;
                }


                // 1. تأكد من وجود الـ SKU وتجاهل المكرر
                $sku = trim($row['product_sku']);
                if (!$sku || Product::where('sku', $sku)->exists()) {
                    continue;
                }

                // 2. إنشاء أو جلب الأقسام (معالجة كل الأقسام من 1 إلى 8)
                $categoryIds = [];
                for ($i = 1; $i <= 8; $i++) {
                    $categoryName = trim($row["product_category_{$i}"]);
                    if ($categoryName) {
                        $category = Category::firstOrCreate(
                            ['name->ar' => $categoryName],
                            ['name' => [
                                // 'internal_id' => $row['category_internal_id'],
                                'ar' => $categoryName,
                                'en' => $categoryName
                            ]]
                        );
                        $categoryIds[] = $category->id;
                    }

                    OrderHelper::assign($category, 'orders');
                }

                // 3. إنشاء أو جلب الماركة
                $brandName = trim($row['product_brand']);
                $brandId = null;
                if ($brandName) {
                    $brand = Brand::firstOrCreate(
                        ['name->ar' => $brandName],
                        [
                            'name' => ['ar' => $brandName, 'en' => $brandName],
                            'image' => '',
                        ]
                    );
                    $brandId = $brand->id;

                    OrderHelper::assign($brand, 'orders');
                }



                // 4. بناء بيانات المنتج
                $product = Product::create([
                    'sku' => $sku,
                    'name' => [
                        'ar' => trim($row['product_name']),
                        'en' => trim($row['product_name']),
                    ],
                    'description' => [
                        'ar' => trim($row['product_description']),
                        'en' => trim($row['product_description']),
                    ],
                    // 'internal_id' => $row['product_internal_id'],
                    'price' => (float) $row['product_price'],
                    'price_after_discount' => (float) $row['product_compare_to_price'] ?: null,
                    'cost_price' => (float) $row['product_cost_price'],
                    // 'cost_price_after_discount' => (float) $row['product_sale_price'] ?: null, // product_sale_price not found
                    // 'cost_price_discount_start' => $row['product_sale_start'] ?: null, // product_sale_start not found
                    // 'cost_price_discount_end' => $row['product_sale_end'] ?: null, // product_sale_end not found
                    'stock_unlimited' => !((bool) $row['product_is_inventory_tracked']),
                    'stock' => (int) $row['product_quantity'],
                    'out_of_stock' => $row['product_quantity_out_of_stock_behaviour'] == 'SHOW' ? 'show_on_storefront' : ($row['product_quantity_out_of_stock_behaviour'] == 'ALLOW_PREORDER' ? 'show_and_allow_pre_order' : 'hide_from_storefront'),
                    'minimum_purchase' => !empty($row['product_quantity_minimum_allowed_for_purchase']) ? (int) $row['product_quantity_minimum_allowed_for_purchase'] : null,
                    'maximum_purchase' => !empty($row['product_quantity_maximum_allowed_for_purchase']) ? (int) $row['product_quantity_maximum_allowed_for_purchase'] : null,
                    'availability' => (bool) $row['product_is_available'],
                    'requires_shipping' => (bool) $row['product_is_shipping_required'],
                    'shipping_type' => $row['product_shipping_type'] == 'GLOBAL_METHODS' ? 'default' : 'free_shipping',
                    'shipping_rate_single' => (float) $row['product_shipping_fixed_rate'],
                    'weight' => (float) $row['product_weight'],
                    'length' => (float) $row['product_length'],
                    'width' => (float) $row['product_width'],
                    'height' => (float) $row['product_height'],
                    'related_category_limit' => (int) $row['product_related_items_random_number_of_items'],
                    'is_recommended' => (bool) $row['product_is_featured'],
                    'ribbon_text' => [
                        'ar' => $row['product_ribbon_text'],
                        'en' => $row['product_ribbon_text'],
                    ],
                    'ribbon_color' => $row['product_ribbon_color'],
                ]);

                /////// product_option_name

                //product_low_stock_notification_quantity  // not used
                // product_is_featured // not used
                // product_price_per_unit // not used // in csv file is empty
                // product_units_in_product // not used // in csv file is empty
                // product_upc // not used // in csv file is empty
                // product_ribbon_text // not used 
                // product_shipping_preparation_time_for_shipping_in_days // not used // in csv file is empty
                // product_shipping_preparation_time_for_pickup_in_minutes // not used // in csv file is empty
                // product_shipping_preparation_time_for_local_delivery_in_minutes // not used // in csv file is empty
                // product_shipping_preparation_time_for_preorders_in_days // not used // in csv file is empty
                // product_shipping_show_delivery_date_on_the_product_page // not used 
                // product_shipping_method_markup // not used 
                // product_shipping_white_list // not used // in csv file is empty
                // product_shipping_black_list // not used // in csv file is empty
                // product_shipping_white_list_countries // not used
                // product_shipping_black_list_countries // not used // in csv file is empty
                // product_tax_class_code // not used
                // product_related_item_ids // not used // in csv file is empty
                // product_related_item_skus // not used // in csv file is empty
                // product_related_items_random // not used
                // product_related_items_random_category // not used // in csv file is empty
                // product_custom_price_enabled // not used // in csv file is empty
                // product_is_review_collection_enabled // not used
                // product_google_product_category_code // not used // in csv file is empty

                // source_store_id // not used
                // url // not used
                // custom_url_slug // not used





                OrderHelper::assign($product, 'orders');

                if (!empty($categoryIds)) {
                    foreach ($categoryIds as $categoryId) {
                        CategoryProduct::create([
                            'category_id' => $categoryId,
                            'product_id' => $product->id,
                        ]);
                    }

                    $product->related_category_id = $categoryIds[0];
                    $product->save();
                }

                if ($brandId) {
                    $product->brands()->attach([$brandId]);
                }


                // seo
                // Create or update SEO
                if (isset($row['product_seo_title']) && isset($row['product_seo_description'])) {
                    $product->seo()->updateOrCreate(
                        [
                            'seoable_type' => Product::class,
                            'seoable_id' => $product->id,
                        ],
                        [
                            'meta_title' => $row['product_seo_title'] ?? null,
                            'meta_description' => $row['product_seo_description'] ?? null,
                            'keywords' =>  null,
                            'image' => null,
                        ]
                    );
                }

                // 5. الصور
                $mainImage = trim($row['product_media_main_image_url']);
                if ($mainImage) {
                    try {
                        $tempFile = tempnam(sys_get_temp_dir(), 'img_');
                        file_put_contents($tempFile, file_get_contents($mainImage));
                        $imagePath = ImageService::storeImage($tempFile, 'products');
                        unlink($tempFile);

                        $media = Media::create([
                            'product_id' => $product->id,
                            'file' => $imagePath,
                            'type' => 'image',
                            'is_main' => true,
                            'orders' => 0,
                        ]);

                        OrderHelper::assign($media, 'orders');
                    } catch (\Exception $e) {
                        continue;
                    }
                }

                // الصور الإضافية
                foreach ($row as $key => $value) {
                    if (Str::startsWith($key, 'product_media_gallery_image_url_') && $value) {
                        try {
                            $tempFile = tempnam(sys_get_temp_dir(), 'img_');
                            file_put_contents($tempFile, file_get_contents($value));
                            $imagePath = ImageService::storeImage($tempFile, 'products');
                            unlink($tempFile);

                            $media = Media::create([
                                'product_id' => $product->id,
                                'file' => $imagePath,
                                'type' => 'image',
                                'is_main' => false,
                                'orders' => 1,
                            ]);

                            OrderHelper::assign($media, 'orders');
                        } catch (\Exception $e) {
                            continue;
                        }
                    }
                }
            }
        });
    }
}
