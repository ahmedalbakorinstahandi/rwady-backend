<?php

namespace App\Http\Requests\OrderPayment;

use App\Http\Requests\BaseFormRequest;

class CreateOrderPaymentRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'order_id' => 'required|exists:orders,id,deleted_at,NULL',
            'amount' => 'required|numeric',
            'message' => 'required|string',
            'status' => 'required|string|in:pending,completed,failed',
            'method' => 'required|string|in:transfer,cash',
            'attached' => 'nullable|string',
        ];
    }
}
