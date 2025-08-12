<?php

namespace App\Models;

use App\Traits\LanguageTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

class OrderProduct extends Model
{
    use HasFactory, HasTranslations, LanguageTrait;

    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'price',
        'cost_price',
        'status',
        'shipping_rate',
        'color_id',
        'promotion_id',
        'promotion_title',
        'promotion_discount_type',
        'promotion_discount_value',
    ];




    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class)->withTrashed();
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function color(): BelongsTo
    {
        return $this->belongsTo(ProductColor::class, 'color_id')->withTrashed();
    }

    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class)->withTrashed();
    }
}
