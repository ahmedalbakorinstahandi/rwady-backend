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

    public function index(array $filters = [])
    {
        // 1. base promotion query + permissions + apply filters (تأكد أن applyFilters يرجع Builder)
        $base = Promotion::query();
        $base = PromotionPermission::filterIndex($base);
    
        $filtered = FilterService::applyFilters(
            $base,
            $filters,
            ['title'],
            ['discount_value', 'min_cart_total'],
            ['start_at', 'end_at', 'created_at'],
            ['type', 'discount_type', 'status'],
            ['type', 'discount_type', 'status'],
            false
        )->select('promotions.*');
    
        // 2. اختصاصي لكل مجموعة
        $qTypeAll = (clone $filtered)->whereIn('type', ['product', 'category']); // كل النتائج
        $qCartLatest = (clone $filtered)->where('type', 'cart_total')->orderByDesc('created_at')->limit(1);
        $qShippingLatest = (clone $filtered)->where('type', 'shipping')->orderByDesc('created_at')->limit(1);
    
        // 3. unionAll (toBase() => Query\Builder)
        $union = $qTypeAll->toBase()
            ->unionAll($qCartLatest->toBase())
            ->unionAll($qShippingLatest->toBase());
    
        // 4. غلف union في subquery حتى يعمل paginate() و order النهائي
        $sub = DB::query()->fromSub($union, 'promotions_union')->select('promotions_union.*');
    
        // ترتيب نهائي حسب نوع (ضع ترتيبك المطلوب هنا)
        // MySQL: FIELD — يضع المنتج/القسم أولًا ثم cart_total ثم shipping
        $sub = $sub->orderByRaw("FIELD(type, 'product','category','cart_total','shipping')")
                   ->orderByDesc('created_at');
    
        // 5. صفحة (pagination) على الـ subquery
        $perPage = $filters['limit'] ?? 20;
        $page = $sub->paginate($perPage);
    
        // 6. eager load للعلاقات الحقيقية على موديل Eloquent وترتيب النتائج حسب الصفحة
        $ids = collect($page->items())->pluck('id')->filter()->values()->all();
    
        if (!empty($ids)) {
            // عدّل قائمة العلاقات هنا إن احتجت
            $with = ['category', 'product', 'media']; // مثال
    
            $models = Promotion::with($with)
                ->whereIn('id', $ids)
                // نحافظ على ترتيب الـ ids كما جت من الصفحة
                ->orderByRaw('FIELD(id, ' . implode(',', $ids) . ')')
                ->get();
    
            // استبدال مجموعة العناصر في الـ paginator بالنماذج المحمّلة
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
