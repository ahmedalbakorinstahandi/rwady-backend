<?php

namespace App\Http\Controllers\Bulk;

use App\Http\Controllers\Controller;
use App\Http\Services\ProductService;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use League\Csv\Writer;
use League\Csv\Reader;
use SplTempFileObject;
use Illuminate\Support\Facades\Storage;

class ProductBulkController extends Controller
{
    /**
     * GET /api/products/export
     * فلاتر اختيارية:
     * - ids[]=, skus[]=, brand_ids[]=, category_ids[]=, availability=true/false, q=بحث بالاسم
     * - format=csv (حالياً CSV)
     */
    public function export(Request $request)
    {

        //request all products or products with products for export  : products_ids as array or null => validate if is array
        $request->validate([
            'products_ids' => 'nullable|array|exists:products,id,deleted_at,NULL|distinct|min:1',
        ]);


        $products_ids = $request->input('products_ids', null);


        if ($products_ids) {
            $query = Product::whereIn('id', $products_ids);
        } else {
            $query = Product::query();
        }



        $q = $query->with(['categories:id', 'brands:id', 'media:id,product_id,path,type,source', 'seo']);

        // فلاتر اختيارية
        if ($request->filled('ids')) {
            $q->whereIn('id', (array) $request->input('ids'));
        }
        if ($request->filled('skus')) {
            $q->whereIn('sku', (array) $request->input('skus'));
        }
        if ($request->filled('availability')) {
            $q->where('availability', $this->toBool($request->input('availability')));
        }
        if ($request->filled('brand_ids')) {
            $brandIds = array_filter(array_map('intval', (array) $request->input('brand_ids')));
            $q->whereHas('brands', fn($b) => $b->whereIn('brands.id', $brandIds));
        }
        if ($request->filled('category_ids')) {
            $catIds = array_filter(array_map('intval', (array) $request->input('category_ids')));
            $q->whereHas('categories', fn($c) => $c->whereIn('categories.id', $catIds));
        }
        if ($request->filled('q')) {
            $term = $request->input('q');
            $q->where(function ($x) use ($term) {
                $x->where('name->ar', 'like', "%{$term}%")
                    ->orWhere('name->en', 'like', "%{$term}%")
                    ->orWhere('sku', 'like', "%{$term}%");
            });
        }

        $products = $q->orderBy('id')->get();

        // تجهيز CSV
        $csv = Writer::createFromFileObject(new SplTempFileObject());

        // الأعمدة المُصدّرة
        $headers = [
            'sku',
            'name_ar',
            'name_en',
            'description_ar',
            'description_en',
            'price',
            'price_after_discount',
            'price_discount_start',
            'price_discount_end',
            'cost_price',
            'cost_price_after_discount',
            'cost_price_discount_start',
            'cost_price_discount_end',
            'availability',
            'stock',
            'stock_unlimited',
            'out_of_stock',
            'minimum_purchase',
            'maximum_purchase',
            'weight',
            'length',
            'width',
            'height',
            'requires_shipping',
            'shipping_type',
            'shipping_rate_single',
            'is_recommended',
            'ribbon_text_ar',
            'ribbon_text_en',
            'ribbon_color',
            'category_ids', // IDs مفرّقة بفاصلة
            'brand_ids',    // IDs مفرّقة بفاصلة
            'media',        // روابط مفرّقة بفاصلة
            // 'internal_url', // الرابط الداخلي الكامل للمنتج
            'related_category_id',
            'related_category_limit',
            'related_products', // IDs مفرّقة بفاصلة
            'seo_meta_title',
            'seo_meta_description',
            'seo_keywords',

            'sort_order',
        ];







        $csv->insertOne($headers);

        foreach ($products as $p) {
            $categoryIds = $p->categories->pluck('id')->implode(',');
            $brandIds = $p->brands->pluck('id')->implode(',');
            $media = $p->media;

            $media_links = [];

            for ($i = 0; $i < count($media); $i++) {
                if ($media[$i]->source == 'file') {
                    $media_links[$i] = 'https://rwady-backend.ahmed-albakor.com/storage/' . $media[$i]->path;
                } else {
                    $media_links[$i] = $media[$i]->path;
                }
            }

            $media = implode("\n", $media_links);

            // ابنِ رابط داخلي (عدّل الحقل/الراوت حسب مشروعك)
            // لو عندك حقل slug:
            // $internalUrl = url('/products/' . ($p->slug ?? $p->id));

            $relatedIds = method_exists($p, 'relatedProducts')
                ? $p->relatedProducts()->pluck('products.id')->implode(',')
                : '';

            $csv->insertOne([
                $p->sku,
                $p->name['ar'],
                $p->name['en'],
                $p->description['ar'],
                $p->description['en'],
                $p->price,
                $p->price_after_discount,
                $p->price_discount_start,
                $p->price_discount_end,
                $p->cost_price,
                $p->cost_price_after_discount,
                $p->cost_price_discount_start,
                $p->cost_price_discount_end,
                $this->fromBool($p->availability),
                $p->stock,
                $this->fromBool($p->stock_unlimited),
                $p->out_of_stock,
                $p->minimum_purchase,
                $p->maximum_purchase,
                $p->weight,
                $p->length,
                $p->width,
                $p->height,
                $this->fromBool($p->requires_shipping),
                $p->shipping_type,
                $p->shipping_rate_single,
                $this->fromBool($p->is_recommended),
                $p->ribbon_text['ar'],
                $p->ribbon_text['en'],
                $p->ribbon_color,
                $categoryIds,
                $brandIds,
                $media,
                // $internalUrl,
                $p->related_category_id,
                $p->related_category_limit,
                $relatedIds,
                optional($p->seo)->meta_title,
                optional($p->seo)->meta_description,
                optional($p->seo)->keywords,
                $p->orders,
            ]);
        }

        $filename = 'products_export_' . now()->format('Ymd_His') . '.csv';
        $csvContent = $csv->toString();

        // Store file locally for 5 minutes
        Storage::put('public/exports/' . $filename, $csvContent);

        // Generate temporary URL valid for 5 minutes
        $url = Storage::temporaryUrl(
            'public/exports/' . $filename,
            now()->addMinutes(5)
        );

        return response()->json([
            'url' => $url
        ]);
    }

    /**
     * POST /api/products/import
     * يقبل ملف CSV باسم "file" في الـ form-data.
     * باراميترات اختيارية:
     * - dry_run=1 (يعرض ماذا سيحدث بدون تعديل قاعدة البيانات)
     * - fallback_missing_relations=ignore|detach (سلوك العلاقات غير الموجودة: افتراضي ignore)
     * - match_by=sku|internal_url (افتراضي sku، لو ما لقي sku يحاول internal_url)
     */
    public function import(Request $request)
    {
        // 1) استلام الملف
        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
        ]);

        $file = $request->file('file')->getRealPath();

        // 2) قراءة CSV بهيدر ديناميكي
        $csv = Reader::createFromPath($file, 'r');
        $csv->setHeaderOffset(0);
        $rows = $csv->getRecords();

        // 3) عدادات وتقارير
        $created = 0;
        $updated = 0;
        $skipped = 0;
        $report = [];

        // 4) Helpers محلية
        $toBool = function ($val) {
            if (is_bool($val)) return $val;
            $val = strtolower(trim((string)$val));
            return in_array($val, ['1', 'true', 'yes', 'y', 'on'], true);
        };
        $nullIfEmpty = function ($val) {
            return (is_null($val) || $val === '') ? null : $val;
        };
        $numOrNull = function ($val) {
            $val = trim((string)$val);
            return ($val === '') ? null : (float)$val;
        };
        $intOrNull = function ($val) {
            $val = trim((string)$val);
            return ($val === '') ? null : (int)$val;
        };
        $numOrZero = function ($val) {
            $val = trim((string)$val);
            return ($val === '') ? 0 : (float)$val;
        };
        $intOrZero = function ($val) {
            $val = trim((string)$val);
            return ($val === '') ? 0 : (int)$val;
        };
        $parseIds = function ($val) {
            if (is_null($val) || $val === '') return [];
            return array_values(array_filter(array_map('intval', preg_split('/[,\s]+/', (string)$val))));
        };
        // تحويل روابط /storage الداخلية إلى مسار نسبي ليُخزن كـ file
        $normalizeMedia = function ($raw) {
            $raw = trim((string)$raw);
            if ($raw === '') return null;
            // مثال backend: https://rwady-backend.ahmed-albakor.com/storage/...
            // لو بدأ بـ http ويحتوي /storage/ ناخذ الجزء بعد /storage/
            if (preg_match('~^https?://[^/]+/storage/(.+)$~i', $raw, $m)) {
                return $m[1]; // path داخل storage
            }
            return $raw; // رابط خارجي كما هو
        };

        foreach ($rows as $i => $row) {
            try {
                // لو فيه type واعمدته فيها product بس، تجاهل غير ذلك
                if (isset($row['type']) && strtolower(trim($row['type'])) !== 'product') {
                    $skipped++;
                    $report[] = "Row #" . ($i + 2) . ": skipped (type != product)";
                    continue;
                }

                // قراءة الحقول (مع تحمّل غياب أعمدة)
                $sku = trim((string)($row['sku'] ?? ''));

                // الأسماء والوصف (نصوص فارغة لو ناقصة)
                $name_ar = (string)($row['name_ar'] ?? '');
                $name_en = (string)($row['name_en'] ?? '');
                $desc_ar = (string)($row['description_ar'] ?? '');
                $desc_en = (string)($row['description_en'] ?? '');

                // شرطك: إذا ما في SKU وكان الاسم العربي فاضي، بننشئ باسم عربي افتراضي
                if ($sku === '' && $name_ar === '') {
                    $name_ar = 'منتج بدون اسم';
                }

                // أرقام "nullable" نخليها null لو فاضية
                $price                     = $numOrNull($row['price'] ?? null);
                $price_after_discount      = $numOrNull($row['price_after_discount'] ?? null);
                $price_discount_start      = $nullIfEmpty($row['price_discount_start'] ?? null);
                $price_discount_end        = $nullIfEmpty($row['price_discount_end'] ?? null);

                $cost_price                = $numOrNull($row['cost_price'] ?? null);
                $cost_price_after_discount = $numOrNull($row['cost_price_after_discount'] ?? null);
                $cost_price_discount_start = $nullIfEmpty($row['cost_price_discount_start'] ?? null);
                $cost_price_discount_end   = $nullIfEmpty($row['cost_price_discount_end'] ?? null);

                $availability              = isset($row['availability']) ? $toBool($row['availability']) : null;
                $stock                     = $intOrNull($row['stock'] ?? null);
                $stock_unlimited           = isset($row['stock_unlimited']) ? $toBool($row['stock_unlimited']) : null;

                $out_of_stock = $row['out_of_stock'] ?? null;
                if ($out_of_stock !== null && !in_array($out_of_stock, ['show_on_storefront', 'hide_from_storefront', 'show_and_allow_pre_order'], true)) {
                    // قيمة غير صالحة -> خليها null
                    $out_of_stock = null;
                }

                $minimum_purchase          = $intOrNull($row['minimum_purchase'] ?? null);
                $maximum_purchase          = $intOrNull($row['maximum_purchase'] ?? null);

                $weight = $numOrNull($row['weight'] ?? null);
                $length = $numOrNull($row['length'] ?? null);
                $width  = $numOrNull($row['width'] ?? null);
                $height = $numOrNull($row['height'] ?? null);

                $requires_shipping = isset($row['requires_shipping']) ? $toBool($row['requires_shipping']) : null;

                $shipping_type = $row['shipping_type'] ?? null;
                if ($shipping_type !== null && !in_array($shipping_type, ['default', 'fixed_shipping', 'free_shipping'], true)) {
                    $shipping_type = null;
                }

                $shipping_rate_single = $numOrNull($row['shipping_rate_single'] ?? null);

                $is_recommended = isset($row['is_recommended']) ? $toBool($row['is_recommended']) : null;

                $ribbon_text_ar = (string)($row['ribbon_text_ar'] ?? '');
                $ribbon_text_en = (string)($row['ribbon_text_en'] ?? '');
                $ribbon_color   = (string)($row['ribbon_color'] ?? '');

                // علاقات
                $category_ids = $parseIds($row['category_ids'] ?? '');
                $brand_ids    = $parseIds($row['brand_ids'] ?? '');
                $related_ids  = $parseIds($row['related_products'] ?? '');

                // فلترة IDs غير الموجودة (نترك الموجودة فقط)
                if (!empty($category_ids)) {
                    $category_ids = Category::whereIn('id', $category_ids)->pluck('id')->all();
                }
                if (!empty($brand_ids)) {
                    $brand_ids = Brand::whereIn('id', $brand_ids)->pluck('id')->all();
                }
                if (!empty($related_ids)) {
                    $related_ids = Product::whereIn('id', $related_ids)->pluck('id')->all();
                }

                $related_category_id    = $intOrNull($row['related_category_id'] ?? null);
                $related_category_limit = $intOrNull($row['related_category_limit'] ?? null);

                // ميديا: سطور متعددة في خلية واحدة (newline)
                $media_raw = (string)($row['media'] ?? '');
                $media_list = [];
                if ($media_raw !== '') {
                    // نفصل بالسطر (يدعم \n أو \r\n)
                    $media_lines = preg_split("/\r\n|\n|\r/", $media_raw);
                    foreach ($media_lines as $m) {
                        $m = trim($m);
                        if ($m !== '') {
                            $norm = $normalizeMedia($m);
                            if ($norm !== null) {
                                $media_list[] = $norm;
                            }
                        }
                    }
                }

                // SEO
                $seo_meta_title       = $nullIfEmpty($row['seo_meta_title'] ?? null);
                $seo_meta_description = $nullIfEmpty($row['seo_meta_description'] ?? null);
                $seo_keywords         = $nullIfEmpty($row['seo_keywords'] ?? null);

                // sort_order
                $sort_order = $intOrNull($row['sort_order'] ?? null);

                // بناء payload حسب Service تبعك (create / update)
                $payload = [
                    'sku'                   => $sku ?: null,
                    'name'                  => ['ar' => $name_ar, 'en' => $name_en],
                    'description'           => ['ar' => $desc_ar, 'en' => $desc_en],

                    'price'                 => $price,
                    'price_after_discount'  => $price_after_discount,
                    'price_discount_start'  => $price_discount_start,
                    'price_discount_end'    => $price_discount_end,

                    'cost_price'                => $cost_price,
                    'cost_price_after_discount' => $cost_price_after_discount,
                    'cost_price_discount_start' => $cost_price_discount_start,
                    'cost_price_discount_end'   => $cost_price_discount_end,

                    'availability'          => $availability,
                    'stock'                 => $stock,
                    'stock_unlimited'       => $stock_unlimited,
                    'out_of_stock'          => $out_of_stock,
                    'minimum_purchase'      => $minimum_purchase,
                    'maximum_purchase'      => $maximum_purchase,

                    'weight'                => $weight,
                    'length'                => $length,
                    'width'                 => $width,
                    'height'                => $height,

                    'requires_shipping'     => $requires_shipping,
                    'shipping_type'         => $shipping_type,
                    'shipping_rate_single'  => $shipping_rate_single,

                    'is_recommended'        => $is_recommended,
                    'ribbon_text'           => ['ar' => $ribbon_text_ar, 'en' => $ribbon_text_en],
                    'ribbon_color'          => $ribbon_color,

                    'categories'            => $category_ids ?: null,
                    'brands'                => $brand_ids ?: null,

                    'media'                 => $media_list ?: null,

                    'related_category_id'   => $related_category_id,
                    'related_category_limit' => $related_category_limit,
                    'related_products'      => $related_ids ?: null,

                    'seo' => [
                        'meta_title'       => $seo_meta_title,
                        'meta_description' => $seo_meta_description,
                        'keywords'         => $seo_keywords,
                        'image'            => null,
                    ],
                ];

                // تنظيف: لو بعض المفاتيح null وما بدك تبعثها
                $payload = array_filter($payload, fn($v) => !is_null($v));

                // 5) وجود SKU؟ حدّث، وإلا أنشئ
                $product = null;
                if ($sku !== '') {
                    $product = Product::where('sku', $sku)->first();
                }

                $productService = new ProductService();

                if ($product) {
                    // Update (يمر عبر UpdateProductRequest داخل Service)
                    $productService->update($payload, $product);
                    $updated++;
                    $report[] = "Row #" . ($i + 2) . ": updated (SKU={$sku})";
                } else {
                    // Create: لو الاسم العربي ما وصل بنعطيه قيمة افتراضية مسبقاً فوق
                    // ملاحظة: Service عندك يولّد SKU إن لم يُرسل، ثم يستبدله بـ id
                    if (isset($sku)) {
                        $payload['sku'] = $sku;
                    }
                    $new = $productService->create($payload);
                    $created++;
                    $report[] = "Row #" . ($i + 2) . ": created (ID={$new->id})";
                }

                // ترتيب اختياري (orders)
                if (!is_null($sort_order)) {
                    // لو عندك OrderHelper::assign يقبل custom
                    // أو مباشرة:
                    $target = isset($product) && $product ? $product->fresh() : $new->fresh();
                    $target->orders = $sort_order;
                    $target->save();
                }
            } catch (\Throwable $e) {
                $skipped++;
                $report[] = "Row #" . ($i + 2) . ": error => " . $e->getMessage();
                continue;
            }
        }

        return response()->json([
            'success' => true,
            'created' => $created,
            'updated' => $updated,
            'skipped' => $skipped,
            'report'  => $report,
        ]);
    }

    /* ===================== Helpers ===================== */

    private function toBool($val)
    {
        if (is_bool($val)) return $val;
        if ($val === null) return null;
        $s = strtolower(trim((string)$val));
        return in_array($s, ['1', 'true', 'yes', 'y', 'on'], true);
    }

    private function fromBool($val)
    {
        return $val ? 'true' : 'false';
    }

    private function toInt($val)
    {
        if ($val === null || $val === '') return null;
        return (int) $val;
    }

    private function toIntOrNull($val)
    {
        if ($val === null || $val === '') return null;
        return is_numeric($val) ? (int) $val : null;
    }

    private function toNumber($val)
    {
        if ($val === null || $val === '') return null;
        return (float) str_replace(',', '', $val);
    }

    private function splitIds(string $s): array
    {
        if (trim($s) === '') return [];
        return array_values(array_unique(
            array_filter(array_map('intval', preg_split('/[,\|;]+/', $s)))
        ));
    }

    private function splitStrings(string $s): array
    {
        if (trim($s) === '') return [];
        return array_values(array_unique(
            array_filter(array_map('trim', preg_split('/[,\|;]+/', $s)))
        ));
    }

    private function extractSlugFromUrl(string $url): ?string
    {
        $path = parse_url($url, PHP_URL_PATH);
        if (!$path) return null;
        // توقّع نمط /products/{slug}
        $parts = array_values(array_filter(explode('/', $path)));
        $idx = array_search('products', $parts);
        if ($idx === false) return null;
        return $parts[$idx + 1] ?? null;
    }
}
