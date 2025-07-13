<?php

namespace App\Http\Requests\Coupon;

use App\Http\Requests\BaseFormRequest;

class UpdateCouponRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'is_active' => 'nullable|boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            // 'code' => 'nullable|string|max:100',
            // 'type' => 'nullable|string|in:fixed,percentage',
            // 'amount' => 'nullable|numeric',
        ];
    }
}
