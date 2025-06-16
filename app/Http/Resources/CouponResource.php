<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CouponResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'type' => $this->type,
            'value' => $this->value,
            'min_order_amount' => $this->min_order_amount,
            'max_discount' => $this->max_discount,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'usage_limit' => $this->usage_limit,
            'is_active' => $this->is_active,
            'usages' => CouponUsageResource::collection($this->whenLoaded('usages')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
} 