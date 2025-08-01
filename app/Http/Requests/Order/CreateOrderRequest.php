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
            'notes' => 'nullable|string',
            'coupon_code' => 'nullable|string',
            'payment_method' => 'required|string|in:qi,cash,installment,transfer',
            'success_url' => 'required_if:payment_method,qi|url',
            'fail_url' => 'required_if:payment_method,qi|url',
            'attached' => 'nullable|string|required_if:payment_method,transfer',
            'identity' => 'nullable|string|required_if:payment_method,installment',

            'direct_order' => 'nullable|boolean',

            'address' => 'nullable|array',
            'address.extra_address' => 'nullable|string|max:255',
            'address.longitude' => 'required|numeric',
            'address.latitude' => 'required|string',
            'address.is_default' => 'nullable|boolean',
        ];
    }
}
