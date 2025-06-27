<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'code',
        'status',
        'payment_fees',
        'notes',
        'payment_method',
        'metadata',
        'payment_session_id',
    ];


    public function getTotalAmountAttribute()
    {
        $productsAmount = $this->orderProducts->sum('price');

        $coupon = CouponUsage::where('order_id', $this->id)->first();

        if ($coupon && $coupon->discount_type && $coupon->discount_value) {
            if ($coupon->discount_type == 'percentage') {
                $productsAmount = $productsAmount - ($productsAmount * ($coupon->discount_value / 100));
            } else {
                $productsAmount = $productsAmount - $coupon->discount_value;
            }
        }

        $productsAmount = $productsAmount + ($productsAmount * ($this->payment_fees / 100));



        return $productsAmount;
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

    public function metadata(): Attribute
    {
        return new Attribute(
            get: fn ($value) => json_decode($value, true),
            set: fn ($value) => json_encode($value),
        );
    }
}
