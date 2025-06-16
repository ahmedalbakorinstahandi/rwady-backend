<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'status_id' => $this->status_id,
            'total_amount' => $this->total_amount,
            'shipping_address_id' => $this->shipping_address_id,
            'billing_address_id' => $this->billing_address_id,
            'notes' => $this->notes,
            'tracking_number' => $this->tracking_number,
            'user' => new UserResource($this->whenLoaded('user')),
            'status' => new StatusResource($this->whenLoaded('status')),
            'shipping_address' => new AddressResource($this->whenLoaded('shippingAddress')),
            'billing_address' => new AddressResource($this->whenLoaded('billingAddress')),
            'products' => OrderProductResource::collection($this->whenLoaded('products')),
            'payments' => OrderPaymentResource::collection($this->whenLoaded('payments')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
} 