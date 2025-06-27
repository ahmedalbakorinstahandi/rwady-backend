<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentSession extends Model
{
    protected $fillable = ['payment_id', 'order_id', 'status', 'metadata'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
