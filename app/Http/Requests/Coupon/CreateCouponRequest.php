<?php

namespace App\Http\Requests\Coupon;

use App\Http\Requests\BaseFormRequest;

class CreateCouponRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'code' => 'required|string|max:100',
            'type' => 'required|string|in:fixed,percentage',
            'amount' => 'required|numeric',
            'is_active' => 'required|boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ];
    }
} 