<?php

namespace App\Http\Controllers\Bulk;

use App\Http\Controllers\Controller;
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
        $q = Product::query()
            ->with(['categories:id', 'brands:id', 'media:id,product_id,path,type,source', 'seo']);

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
            'cost_price',
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
                $p->cost_price,
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
        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
            'dry_run' => 'sometimes|in:0,1',
            'fallback_missing_relations' => 'sometimes|in:ignore,detach',
            'match_by' => 'sometimes|in:sku,internal_url',
        ]);

        $dryRun = (bool) $request->boolean('dry_run', false);
        $fallbackMissing = $request->input('fallback_missing_relations', 'ignore');
        $matchBy = $request->input('match_by', 'sku');

        $csv = Reader::createFromPath($request->file('file')->getRealPath(), 'r');
        $csv->setHeaderOffset(0);
        $rows = $csv->getRecords();

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $errors = [];

        foreach ($rows as $idx => $row) {
            try {
                $sku = trim((string)($row['sku'] ?? ''));
                $internalUrl = trim((string)($row['internal_url'] ?? ''));

                // محاولة إيجاد المنتج
                $product = null;

                if ($matchBy === 'sku' && $sku !== '') {
                    $product = Product::where('sku', $sku)->first();
                }

                if (!$product && $internalUrl !== '') {
                    $slug = $this->extractSlugFromUrl($internalUrl);
                    if ($slug) {
                        // عدّل هذا حسب حقلك: slug أو custom_url_slug
                        $product = Product::where('slug', $slug)->first();
                    }
                }

                // بناء الداتا
                $data = [
                    // الاسم مطلوب بالعربي عند الإنشاء فقط:
                    'name' => [
                        'ar' => $row['name_ar'] ?? ($product ? $product->getTranslation('name', 'ar') : 'منتج بدون اسم'),
                        'en' => $row['name_en'] ?? ($product ? $product->getTranslation('name', 'en') : ''),
                    ],
                    'description' => [
                        'ar' => $row['description_ar'] ?? ($product ? $product->getTranslation('description', 'ar') : ''),
                        'en' => $row['description_en'] ?? ($product ? $product->getTranslation('description', 'en') : ''),
                    ],
                    'price' => $this->toNumber($row['price'] ?? null),
                    'price_after_discount' => $this->toNumber($row['price_after_discount'] ?? null),
                    'cost_price' => $this->toNumber($row['cost_price'] ?? null),
                    'availability' => $this->toBool($row['availability'] ?? null),
                    'stock' => $this->toInt($row['stock'] ?? null),
                    'stock_unlimited' => $this->toBool($row['stock_unlimited'] ?? null),
                    'out_of_stock' => $row['out_of_stock'] ?? ($product->out_of_stock ?? 'show_on_storefront'),
                    'minimum_purchase' => $this->toInt($row['minimum_purchase'] ?? null),
                    'maximum_purchase' => $this->toInt($row['maximum_purchase'] ?? null),
                    'weight' => $this->toNumber($row['weight'] ?? null),
                    'length' => $this->toNumber($row['length'] ?? null),
                    'width' => $this->toNumber($row['width'] ?? null),
                    'height' => $this->toNumber($row['height'] ?? null),
                    'shipping_type' => $row['shipping_type'] ?? ($product->shipping_type ?? 'default'),
                    'shipping_rate_single' => $this->toNumber($row['shipping_rate_single'] ?? null),
                    'is_recommended' => $this->toBool($row['is_recommended'] ?? null),
                    'ribbon_text' => [
                        'ar' => $row['ribbon_text_ar'] ?? ($product ? $product->getTranslation('ribbon_text', 'ar') : ''),
                        'en' => $row['ribbon_text_en'] ?? ($product ? $product->getTranslation('ribbon_text', 'en') : ''),
                    ],
                    'ribbon_color' => $row['ribbon_color'] ?? ($product->ribbon_color ?? null),
                    'related_category_id' => $this->toIntOrNull($row['related_category_id'] ?? null),
                ];

                // علاقات: أقسام، براندات، ميديا، منتجات مرتبطة
                $categoryIds = $this->splitIds($row['category_ids'] ?? '');
                $brandIds    = $this->splitIds($row['brand_ids'] ?? '');
                $media       = $this->splitStrings($row['media'] ?? '');
                $relatedIds  = $this->splitIds($row['related_products'] ?? '');

                // تأكد أن IDs موجودة فعلاً
                $categoryIds = Category::whereIn('id', $categoryIds)->pluck('id')->all();
                $brandIds    = Brand::whereIn('id', $brandIds)->pluck('id')->all();
                $relatedIds  = Product::whereIn('id', $relatedIds)->pluck('id')->all();

                // SEO
                $seo = [
                    'meta_title' => $row['seo_meta_title'] ?? null,
                    'meta_description' => $row['seo_meta_description'] ?? null,
                ];

                // تنفيذ
                if ($product) {
                    if ($dryRun) {
                        $updated++;
                        continue;
                    }

                    // تحديث باستخدام سيرفيسك الحالي
                    $payload = array_merge($data, [
                        'categories' => $categoryIds,
                        'brands'     => $brandIds,
                        'media'      => $media,
                        'related_products' => $relatedIds,
                        'seo'        => $seo,
                    ]);

                    // Request-layer validation عندك بتتقبل null/"" وتتعامل معهم
                    app('App\Services\ProductService')->update($payload, $product);
                    $updated++;
                } else {
                    if ($dryRun) {
                        $created++;
                        continue;
                    }

                    // إنشاء جديد
                    $payload = array_merge($data, [
                        'sku'        => $sku ?: null, // ممكن فاضي، وسيرفيسك يولّد واحد
                        'categories' => $categoryIds,
                        'brands'     => $brandIds,
                        'media'      => $media,
                        'related_products' => $relatedIds,
                        'seo'        => $seo,
                    ]);

                    app('App\Services\ProductService')->create($payload);
                    $created++;
                }
            } catch (\Throwable $e) {
                $skipped++;
                $errors[] = [
                    'row' => $idx + 1,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'ok' => true,
            'dry_run' => $dryRun,
            'created' => $created,
            'updated' => $updated,
            'skipped' => $skipped,
            'errors'  => $errors,
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
