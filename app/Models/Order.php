<?php

namespace App\Models;

use App\Traits\LanguageTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Spatie\Translatable\HasTranslations;

class Order extends Model
{
    use HasFactory, HasTranslations, LanguageTrait;

    protected $fillable = [
        'user_id',
        'code',
        'status',
        'payment_fees',
        'notes',
        'payment_method',
        'metadata',
        'payment_session_id',
        'promotion_cart_id',
        'promotion_cart_title',
        'promotion_cart_discount_value',
        'promotion_cart_discount_type',
        'promotion_free_shipping',
        'promotion_shipping_id',
        'promotion_shipping_title',
    ];

    public $translatable = ['promotion_cart_title', 'promotion_shipping_title'];

    protected function promotionCartTitle(): Attribute
    {
        return Attribute::make(
            get: fn(string $value) => $this->getAllTranslations('promotion_cart_title'),
        );
    }

    protected function promotionShippingTitle(): Attribute
    {
        return Attribute::make(
            get: fn(string $value) => $this->getAllTranslations('promotion_shipping_title'),
        );
    }


    public function getTotalAmountAttribute()
    {

        $totalAmount = 0;

        // products amount with quantity for each product
        $productsData = $this->orderProducts->map(function ($product) {
            return [
                'price' => $product->price,
                'quantity' => $product->quantity,
            ];
        });

        $productsAmount = $productsData->sum(function ($product) {
            return $product['price'] * $product['quantity'];
        });

        $totalAmount = $productsAmount;

        $coupon = $this->couponUsage;

        $shippingFees = $this->orderProducts->sum('shipping_rate');

        $totalAmount = $totalAmount + $shippingFees;



        if ($coupon) {
            if ($coupon->discount_type == 'percentage') {
                $totalAmount = $totalAmount - ($totalAmount * ($coupon->discount_value / 100));
            } else {
                $totalAmount = $totalAmount - $coupon->discount_value;
            }
        }

        $totalAmount = $totalAmount + ($totalAmount * ($this->payment_fees / 100));




        if ($this->promotion_cart_id) {
            if ($this->promotion_cart_discount_type == 'percentage') {
                $totalAmount = $totalAmount - ($totalAmount * ($this->promotion_cart_discount_value / 100));
            } else {
                $totalAmount = $totalAmount - $this->promotion_cart_discount_value;
            }
        }

        
        return $totalAmount;
    }



    // order payment
    public function getTotalAmountPaidAttribute()
    {
        return $this->payments->sum('amount');
    }

    // paid status
    public function getPaidStatusAttribute()
    {
        return $this->statuses->where('status', 'paid')->first();
    }


    // coupon usage
    public function couponUsage(): BelongsTo
    {
        return $this->belongsTo(CouponUsage::class);
    }


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }


    public function statuses(): MorphMany
    {
        return $this->morphMany(Status::class, 'statusable');
    }


    public function orderProducts(): HasMany
    {
        return $this->hasMany(OrderProduct::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(OrderPayment::class);
    }

    // morpth has one
    public function address(): MorphOne
    {
        return $this->morphOne(Address::class, 'addressable');
    }

    public function metadata(): Attribute
    {
        return new Attribute(
            get: fn($value) => json_decode($value, true),
            set: fn($value) => json_encode($value),
        );
    }


    // promotion
    public function promotionCart(): BelongsTo
    {
        return $this->belongsTo(Promotion::class, 'promotion_cart_id');
    }

    public function promotionShipping(): BelongsTo
    {
        return $this->belongsTo(Promotion::class, 'promotion_shipping_id');
    }
}
