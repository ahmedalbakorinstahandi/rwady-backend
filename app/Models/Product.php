<?php

namespace App\Models;

use App\Traits\LanguageTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Product extends Model
{
    use HasFactory, SoftDeletes, HasTranslations, LanguageTrait;

    protected $fillable = [
        'name',
        'description',
        'sku',
        'price',
        'price_after_discount',
        'price_discount_start',
        'price_discount_end',
        'cost_price',
        'cost_price_after_discount',
        'cost_price_discount_start',
        'cost_price_discount_end',
        'stock',
        'minimum_purchase',
        'maximum_purchase',
        'requires_shipping',
        'weight',
        'length',
        'width',
        'height',
        'shipping_type',
        'shipping_rate_single',
        'shipping_rate_multi',
        'is_recommended',
        'availability',
        'stock_unlimited',
        'out_of_stock',
        'ribbon_text',
        'ribbon_color',
        'related_category_id',
        'related_category_limit',
        'orders',
    ];

    protected $translatable = ['name', 'description', 'ribbon_text'];

    protected $casts = [
        'price' => 'float',
        'price_after_discount' => 'float',
        'cost_price' => 'float',
        'cost_price_after_discount' => 'float',
        'stock' => 'integer',
        'minimum_purchase' => 'integer',
        'maximum_purchase' => 'integer',
        'requires_shipping' => 'boolean',
        'weight' => 'float',
        'length' => 'float',
        'width' => 'float',
        'height' => 'float',
        'shipping_rate_single' => 'float',
        'shipping_rate_multi' => 'float',
        'is_recommended' => 'boolean',
        'availability' => 'boolean',
        'stock_unlimited' => 'boolean',
        'price_discount_start' => 'datetime',
        'price_discount_end' => 'datetime',
        'cost_price_discount_start' => 'datetime',
        'cost_price_discount_end' => 'datetime',
        'out_of_stock' => 'string',
    ];

    // current price after discount if exists and is between start and end date
    public function getFinalPriceAttribute()
    {
        if ($this->price_after_discount > 0 && $this->price_discount_start && $this->price_discount_end && $this->price_discount_start <= now() && $this->price_discount_end >= now()) {
            return $this->price_after_discount;
        }
        return $this->price;
    }

    // current cost price after discount if exists and is between start and end date
    public function getFinalCostPriceAttribute()
    {
        if ($this->cost_price_after_discount > 0 && $this->cost_price_discount_start && $this->cost_price_discount_end && $this->cost_price_discount_start <= now() && $this->cost_price_discount_end >= now()) {
            return $this->cost_price_after_discount;
        }
        return $this->cost_price;
    }

    // get shipping rate 
    public function getShippingRateAttribute($quantity = 1)
    {
        if ($this->shipping_type == 'fixed_shipping') {
            if ($quantity == 1) {
                return $this->shipping_rate_single;
            } else {
                return $this->shipping_rate_multi;
            }
        } elseif ($this->shipping_type == 'free_shipping') {
            return null;
        } elseif ($this->shipping_type == 'default') {
            $defaultShippingRate = Setting::where('key', 'default_shipping_rate_single')->first();

            if ($defaultShippingRate) {
                return (int) $defaultShippingRate->value;
            } else {
                return null;
            }
        }
    }

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn(string $value) => $this->getAllTranslations('name'),
        );
    }



    protected function description(): Attribute
    {
        return Attribute::make(
            get: fn(?string $value) => $this->getCleanDescription(),
        );
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

    protected function ribbonText(): Attribute
    {
        return Attribute::make(
            get: fn(?string $value) => $this->getAllTranslations('ribbon_text'),
        );
    }


    public function getDiscountPercentageAttribute()
    {
        if (($this->price_after_discount > 0 || $this->price_after_discount !=  null) && $this->price_discount_start && $this->price_discount_end && $this->price_discount_start <= now() && $this->price_discount_end >= now()) {
            $value = round($this->price - $this->price_after_discount, 2);
            return [
                'ar' => "وفر {$value}",
                'en' => "Save {$value}",
            ];
        } else {
            return [
                'ar' => null,
                'en' => null,
            ];
        }
    }

    public function getTotalOrdersAttribute()
    {
        return $this->orderProducts()->where('status', 'completed')->count();
    }

    public function relatedCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'related_category_id');
    }

    public function getRelatedCategoryProductsAttribute()
    {
        $query = Product::query()
            ->where('id', '!=', $this->id)
            ->with(['media', 'colors']);

        if ($this->related_category_id === null) {
            return collect();
        }

        if ($this->related_category_id === 0) {
            $query->whereHas('categories');
        } else {
            $query->whereHas('categories', function ($q) {
                $q->where('category_id', $this->related_category_id);
            });
        }

        return $query->inRandomOrder()
            ->limit($this->related_category_limit)
            ->get();
    }


    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_products', 'product_id', 'category_id');
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function brands(): BelongsToMany
    {
        return $this->belongsToMany(Brand::class, 'brand_products', 'product_id', 'brand_id');
    }

    public function colors(): HasMany
    {
        return $this->hasMany(ProductColor::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function orderProducts(): HasMany
    {
        return $this->hasMany(OrderProduct::class);
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(UserFavorite::class);
    }

    public function relatedProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'related_product_items', 'product_id', 'related_product_id');
    }

    public function media(): HasMany
    {
        return $this->hasMany(Media::class);
    }

    public function seo(): MorphOne
    {
        return $this->morphOne(Seo::class, 'seoable');
    }
}
