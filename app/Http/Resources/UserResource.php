<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'phone' => $this->phone,
            'avatar' => $this->avatar,
            'status' => $this->status,
            'role' => $this->role,
            'otp' => $this->otp,
            'otp_expire_at' => $this->otp_expire_at,
            'is_verified' => $this->is_verified,
            'language' => $this->language,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'favorites' => ProductResource::collection($this->whenLoaded('favorites')),
            'addresses' => AddressResource::collection($this->whenLoaded('addresses')),
            'cart_items' => CartItemResource::collection($this->whenLoaded('cartItems')),
            'orders' => OrderResource::collection($this->whenLoaded('orders')),
        ];
    }
}
