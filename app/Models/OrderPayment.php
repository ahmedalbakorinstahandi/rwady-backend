<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'amount',
        'description',
        'status',
        'is_refund',
        'method',
        'attached',
        'metadata'
    ];

    protected $casts = [
        'is_refund' => 'boolean',
        'order_id' => 'integer',
        'amount' => 'float',
        'description' => 'string',
        'status' => 'string',
        'method' => 'string',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    protected function description(): Attribute
    {
        return Attribute::make(
            get: fn(string $value) => json_decode($value, true),
            set: fn($value) => json_encode($value),
        );
    }
    protected function metadata(): Attribute
    {
        return Attribute::make(
            get: fn(string $value) => json_decode($value, true),
            set: fn($value) => json_encode($value),
        );
    }
}
