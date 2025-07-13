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
            'amount' => $this->amount,
            'description' => $this->description,
            'status' => $this->status,
            'is_refund' => $this->is_refund,
            'method' => $this->method,
            'attached' => $this->attached,
            'metadata' => $this->metadata,
            'order' => new OrderResource($this->whenLoaded('order')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
} 