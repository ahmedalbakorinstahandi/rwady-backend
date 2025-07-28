<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Promotion extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'type',
        'discount_type',
        'discount_value',
        'min_cart_total',
        'start_at',
        'end_at',
        'status',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'min_cart_total' => 'decimal:2',
        'discount_value' => 'decimal:2',
    ];

    // Relationships

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            Category::class,
            'promotion_categories',
            'promotion_id',
            'category_id'
        );
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            'promotion_products',
            'promotion_id',
            'product_id'
        );
    }
}
