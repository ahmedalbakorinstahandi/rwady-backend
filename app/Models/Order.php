<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
    ];


    public function getTotalAmountWithOrWithoutFeesAttribute()
    {
        $productsAmount = $this->products->sum('price');

        $coupon = CouponUsage::where('order_id', $this->id)->first();

        if ($coupon && $coupon->discount_type && $coupon->discount_value) {
            if ($coupon->discount_type == 'percentage') {
                $productsAmount = $productsAmount - ($productsAmount * ($coupon->discount_value / 100));
            } else {
                $productsAmount = $productsAmount - $coupon->discount_value;
            }
        }


        return $productsAmount;
    }

    // order payment
    public function getTotalAmountPaidAttribute()
    {
        return $this->payments->sum('amount');
    }

    public function getTotalAmountWithFeesAttribute()
    {
        return $this->total_amount_with_or_without_fees + ($this->total_amount_with_or_without_fees * ($this->payment_fees / 100));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }


    public function products(): HasMany
    {
        return $this->hasMany(OrderProduct::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(OrderPayment::class);
    }
}
