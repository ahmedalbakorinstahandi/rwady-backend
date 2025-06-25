<?php

namespace App\Http\Requests\CartItem;

use App\Http\Requests\BaseFormRequest;

class CreateCartItemRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'product_id' => 'required|exists:products,id,deleted_at,NULL',
            'quantity' => 'required|integer|min:1',
            'color_id' => 'nullable|exists:product_colors,id,deleted_at,NULL',
        ];
    }
}
