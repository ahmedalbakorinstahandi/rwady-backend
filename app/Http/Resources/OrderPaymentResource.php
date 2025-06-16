<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderPaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'payment_method_id' => $this->payment_method_id,
            'amount' => $this->amount,
            'transaction_id' => $this->transaction_id,
            'status' => $this->status,
            'payment_details' => $this->payment_details,
            'order' => new OrderResource($this->whenLoaded('order')),
            'payment_method' => new PaymentMethodResource($this->whenLoaded('paymentMethod')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
} 