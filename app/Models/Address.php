<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Country;
use App\Models\City;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'address',
        'extra_address',
        'country',
        'city',
        'state',
        'zipe_code',
        'longitude',
        'latitude',
        'addressable_id',
        'addressable_type',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'country' => 'integer',
        'city' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shippingOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'shipping_address_id');
    }

    public function billingOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'billing_address_id');
    }

    public function countryInfo(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country', 'id');
    }

    public function cityInfo(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city', 'id');
    }
} 