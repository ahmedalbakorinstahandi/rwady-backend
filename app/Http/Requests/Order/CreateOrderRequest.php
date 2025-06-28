<?php

namespace App\Http\Requests\Order;

use App\Http\Requests\BaseFormRequest;

class CreateOrderRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'products' => 'required|array',
            'products.*.product_id' => 'required|exists:products,id,deleted_at,NULL',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.color' => 'nullable|exists:product_colors,color,deleted_at,NULL',
            'success_url' => 'required|url',
            'fail_url' => 'required|url',
            'notes' => 'nullable|string',
            'coupon_code' => 'nullable|string',
            'payment_method' => 'required|string|in:qi,cash,installment,transfer',
            'attached' => 'nullable|string|required_if:payment_method,transfer',
        ];
    }
}
