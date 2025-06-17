<?php

namespace App\Http\Requests\Category;

use App\Http\Requests\BaseFormRequest;

class AssignProductsToCategoryRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'product_ids' => 'required|array|min:1',
            'product_ids.*' => 'required|integer|exists:products,id,deleted_at,NULL',
        ];
    }
}
