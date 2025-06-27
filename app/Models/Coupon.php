<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'type',
        'amount',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_active' => 'boolean',
        'type' => 'string',
        'amount' => 'float',
    ];

    public function getDiscountAmountLabelAttribute()
    {
        if ($this->type == 'percentage') {
            return $this->amount . '%';
        } else {
            return $this->amount . ' IQD';
        }
    }

    public function getDiscountAmountValueAttribute()
    {
        if ($this->type == 'percentage') {
            return $this->amount;
        } else {
            return $this->amount;
        }
    }

    public function getIsActiveAttribute($value)
    {
        if ($this->start_date && $this->end_date) {
            return $this->start_date <= now() && $this->end_date >= now();
        }
        return true;
    }

    public function usages(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }
}
