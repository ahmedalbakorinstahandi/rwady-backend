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
            'code' => $this->code,
            'status' => $this->status,
            'payment_fees' => $this->payment_fees,
            'total_amount' => $this->total_amount,
            'total_amount_paid' => $this->total_amount_paid,
            'notes' => $this->notes,
            'payment_method' => $this->payment_method,
            'payment_session_id' => $this->payment_session_id,
            'paid_status' => $this->paid_status,
            'metadata' => $this->metadata,
            'user' => new UserResource($this->whenLoaded('user')),
            'order_products' => OrderProductResource::collection($this->whenLoaded('orderProducts')),
            'payments' => OrderPaymentResource::collection($this->whenLoaded('payments')),
            'statuses' => StatusResource::collection($this->whenLoaded('statuses')),
            'coupon_usage' => new CouponUsageResource($this->whenLoaded('couponUsage')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
