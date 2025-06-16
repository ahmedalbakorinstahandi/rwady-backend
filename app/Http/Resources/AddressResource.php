<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AddressResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'address_line1' => $this->address_line1,
            'address_line2' => $this->address_line2,
            'city' => $this->city,
            'state' => $this->state,
            'postal_code' => $this->postal_code,
            'country' => $this->country,
            'is_default' => $this->is_default,
            'phone' => $this->phone,
            'name' => $this->name,
            'user' => new UserResource($this->whenLoaded('user')),
            'shipping_orders' => OrderResource::collection($this->whenLoaded('shippingOrders')),
            'billing_orders' => OrderResource::collection($this->whenLoaded('billingOrders')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
} 