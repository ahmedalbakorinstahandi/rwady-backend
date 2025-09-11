<?php

namespace App\Http\Requests\Order;

use App\Http\Requests\BaseFormRequest;

class RefundOrderRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'amount' => 'required|numeric|min:0',
            'reason' => 'required|string|max:255',
            'method' => 'required|string|in:qi,transfer,cash',
            'attached' => 'string|max:255',
        ];
    }
}
