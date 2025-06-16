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

class Product extends Model
{
    use HasFactory, SoftDeletes, HasTranslations, LanguageTrait;

    protected $fillable = [
        'name',
        'description',
        'sku',
        'price',
        'price_after_discount',
        'cost_price',
        'cost_price_after_discount',
        'stock',
        'minimum_purchase',
        'maximum_purchase',
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
        'price_discount_start',
        'price_discount_end',
        'cost_price_discount_start',
        'cost_price_discount_end',
        'ribbon_text',
        'ribbon_color',
        'related_category_id'
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
    ];

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn(string $value) => $this->getAllTranslations('name'),
        );
    }

    protected function description(): Attribute
    {
        return Attribute::make(
            get: fn(?string $value) => $this->getAllTranslations('description'),
        );
    }

    protected function ribbonText(): Attribute
    {
        return Attribute::make(
            get: fn(?string $value) => $this->getAllTranslations('ribbon_text'),
        );
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'related_category_id');
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

    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'model');
    }

    public function seo()
    {
        return $this->morphOne(Seo::class, 'model');
    }
}
