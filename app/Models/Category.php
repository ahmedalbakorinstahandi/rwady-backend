<?php

namespace App\Models;

use App\Traits\LanguageTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Category extends Model
{
    use HasFactory, SoftDeletes, LanguageTrait, HasTranslations;

    protected $fillable = [
        'name',
        'description',
        'image',
        'availability',
        'parent_id',
        'orders',
    ];


    public $translatable = ['name', 'description'];



    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn(string $value) => $this->getAllTranslations('name'),
        );
    }

    protected function description(): Attribute
    {
        $descType = request()->input('desc_type', 'html');

        if ($descType == 'html') {
            return Attribute::make(
                get: fn(?string $value) => $this->getDescriptionWithAllLocales(),
            );
        } else {
            return Attribute::make(
                get: fn(?string $value) => $this->getCleanDescription(),
            );
        }
    }

    private function getDescriptionWithAllLocales()
    {
        $translations = $this->getAllTranslations('description');
        $allLocales = ['ar', 'en']; // اللغات المدعومة

        // إذا كانت الترجمات فارغة أو null أو غير موجودة، نرجع object مع قيم فارغة
        if (empty($translations) || !is_array($translations) || $translations === null) {
            $result = [];
            foreach ($allLocales as $locale) {
                $result[$locale] = '';
            }
            return $result;
        }

        $result = [];
        foreach ($allLocales as $locale) {
            $result[$locale] = $translations[$locale] ?? '';
        }

        return $result;
    }

    private function getCleanDescription()
    {
        $translations = $this->getAllTranslations('description');
        $allLocales = ['ar', 'en']; // اللغات المدعومة

        // إذا كانت الترجمات فارغة أو null أو غير موجودة، نرجع object مع قيم فارغة
        if (empty($translations) || !is_array($translations) || $translations === null) {
            $result = [];
            foreach ($allLocales as $locale) {
                $result[$locale] = '';
            }
            return $result;
        }

        $cleanTranslations = [];
        foreach ($allLocales as $locale) {
            $html = $translations[$locale] ?? '';
            if (is_string($html)) {
                // Remove HTML tags and decode HTML entities
                $cleanText = strip_tags($html);
                $cleanText = html_entity_decode($cleanText, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $cleanText = trim($cleanText);
                $cleanTranslations[$locale] = $cleanText;
            } else {
                $cleanTranslations[$locale] = '';
            }
        }

        return $cleanTranslations;
    }

    public function getCleanDescriptionAttribute()
    {
        return $this->getCleanDescription();
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->with('children');
    }



    public function products()
    {
        return $this->belongsToMany(
            Product::class,
            'category_products',
            'category_id',
            'product_id'
        );
    }

    public function seo(): MorphOne
    {
        return $this->morphOne(Seo::class, 'seoable');
    }

    public function getBestPromotionAttribute()
    {
        // 1. ترويجات القسم المباشرة
        $categoryPromotions = Promotion::where('type', 'category')
            ->where('status', 'active')
            ->where('start_at', '<=', now())
            ->where('end_at', '>=', now())
            ->whereHas('categories', function ($query) {
                $query->where('category_id', $this->id);
            })
            ->get();

        // 2. ترويجات الأقسام الأب (إذا كان القسم له أب)
        $parentCategoryPromotions = collect();
        if ($this->parent_id) {
            $parentCategoryPromotions = Promotion::where('type', 'category')
                ->where('status', 'active')
                ->where('start_at', '<=', now())
                ->where('end_at', '>=', now())
                ->whereHas('categories', function ($query) {
                    $query->where('category_id', $this->parent_id);
                })
                ->get();
        }

        // 3. دمج الترويجات
        $allPromotions = $categoryPromotions->merge($parentCategoryPromotions);

        if ($allPromotions->isEmpty()) {
            return null;
        }

        // 4. احسب قيمة الخصم لكل ترويج
        // للأقسام، نستخدم متوسط سعر المنتجات في القسم أو قيمة ثابتة
        $averageProductPrice = $this->products()->avg('final_price') ?? 0;
        
        $allPromotions = $allPromotions->map(function ($promotion) use ($averageProductPrice) {
            $promotion->calculated_discount = $promotion->discount_type === 'fixed'
                ? (float)$promotion->discount_value
                : ($promotion->discount_value / 100) * $averageProductPrice;
            return $promotion;
        });

        // 5. اختَر الترويج ذو أعلى قيمة خصم
        return $allPromotions->sortByDesc('calculated_discount')->first();
    }

    
}
