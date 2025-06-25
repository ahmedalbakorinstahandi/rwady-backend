<?php

namespace App\Http\Requests\CartItem;

use App\Http\Requests\BaseFormRequest;

class UpdateCartItemRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'quantity' => 'required|integer|min:1',
        ];
    }
} 