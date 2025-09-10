<?php

namespace App\Http\Services;

use App\Http\Permissions\PromotionPermission;
use App\Models\Promotion;
use App\Services\FilterService;
use App\Services\LanguageService;
use App\Services\MessageService;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class PromotionService
{

    public function index(array $filters = []): LengthAwarePaginator
    {
        // 1) بناء base query + صلاحيات
        $base = Promotion::query();
        $base = PromotionPermission::filterIndex($base);

        // 2) تطبيق الفلاتر (نفترض FilterService::applyFilters يرجع Eloquent\Builder)
        $filtered = FilterService::applyFilters(
            $base,
            $filters,
            // searchable columns
            ['title'],
            // numeric filters (min/max)
            ['discount_value', 'min_cart_total'],
            // date filters
            ['start_at', 'end_at', 'created_at'],
            // allowed filters (exact match)
            ['type', 'discount_type', 'status'],
            // allowed sorts (same as filters here)
            ['type', 'discount_type', 'status'],
            // use pagination inside service? false => return builder
            false
        )->select('promotions.*');

        // 3) استعلامات كل نوع
        $qTypeAll = (clone $filtered)->whereIn('type', ['product', 'category']);
        $qCartLatest = (clone $filtered)->where('type', 'cart_total')->orderByDesc('created_at')->limit(1);
        $qShippingLatest = (clone $filtered)->where('type', 'shipping')->orderByDesc('created_at')->limit(1);

        // 4) unionAll (toBase => Query\Builder)
        $union = $qTypeAll->toBase()
            ->unionAll($qCartLatest->toBase())
            ->unionAll($qShippingLatest->toBase());

        // 5) غلف union في subquery حتى يعمل paginate و order نهائي
        $sub = DB::query()->fromSub($union, 'promotions_union')->select('promotions_union.*');

        // ترتيب نهائي: وضع product/category أولاً ثم cart_total ثم shipping، ثم الأنسب حسب created_at
        // (MySQL FIELD) — لو PostgreSQL استبدل بـ CASE WHEN
        $sub = $sub->orderByRaw("FIELD(type, 'product','category','cart_total','shipping')")
                   ->orderByDesc('created_at');

        // 6) paginate
        $perPage = (int) ($filters['limit'] ?? 20);
        $page = $sub->paginate($perPage);

        // 7) eager load للعلاقات الحقيقية على Eloquent بعد paginate للحفاظ على الأداء وتجنب union-with()
        $ids = collect($page->items())->pluck('id')->filter()->values()->all();

        if (!empty($ids)) {
            // علاقات متوقعة — عدّل حسب موديلك الفعلي
            $possibleRelations = [
                'categories', // لديك relation as BelongsToMany
                'products',   // لديك relation as BelongsToMany
                'media',      // مثال: morphMany
                // أضف هنا أي relation أخرى متوقعة مثل 'creator', 'translations' ...
            ];

            // احتياط: خذ فقط العلاقات المعرفة فعلياً في الموديل لتفادي RelationNotFoundException
            $with = array_values(array_filter($possibleRelations, function ($relation) {
                return method_exists(Promotion::class, $relation);
            }));

            // جلب النماذج الحقيقية مع الحفاظ على ترتيب الصفحات
            $orderedIds = implode(',', $ids); // آمن لأن ids من قاعدة بيانات (أرقام صحيحة)
            $modelsQuery = Promotion::query();

            if (!empty($with)) {
                $modelsQuery = $modelsQuery->with($with);
            }

            $models = $modelsQuery
                ->whereIn('id', $ids)
                ->orderByRaw("FIELD(id, {$orderedIds})")
                ->get();

            // استبدال collection داخل paginator بالنماذج المحمّلة
            if ($page instanceof LengthAwarePaginator) {
                $page->setCollection($models);
            }
        }

        return $page;
    }
    

    public function show($id)
    {
        $promotion = Promotion::with(['categories', 'products'])->where('id', $id)->first();

        if (!$promotion) {
            MessageService::abort(404, 'messages.promotion.not_found');
        }

        PromotionPermission::show($promotion);

        return $promotion;
    }

    public function create($data)
    {
        $data = LanguageService::prepareTranslatableData($data, new Promotion);


        // if type is shipping, set discount_type to fixed and discount_value to 0
        if ($data['type'] == 'shipping') {
            $data['discount_type'] = 'percentage';
            $data['discount_value'] = 100;
        }

        $promotion = Promotion::create($data);

        if (isset($data['categories'])) {
            $categoryData = array_fill_keys($data['categories'], []);
            $promotion->categories()->sync($categoryData);
        }

        if (isset($data['products'])) {
            $productData = array_fill_keys($data['products'], []);
            $promotion->products()->sync($productData);
        }


        $promotion = $this->show($promotion->id);

        return $promotion;
    }

    public function  update($promotion, $data)
    {
        $data = LanguageService::prepareTranslatableData($data, $promotion);

        $promotion->update($data);

        if (isset($data['categories'])) {
            $categoryData = array_fill_keys($data['categories'], []);
            $promotion->categories()->sync($categoryData);
        }

        if (isset($data['products'])) {
            $productData = array_fill_keys($data['products'], []);
            $promotion->products()->sync($productData);
        }

        $promotion = $this->show($promotion->id);


        return $promotion;
    }

    public function delete($promotion)
    {
        $promotion->products()->detach();
        $promotion->categories()->detach();
        $promotion->delete();

        return true;
    }
}
