<?php

namespace App\Http\Requests\Category;

use App\Http\Requests\BaseFormRequest;
 
class AssignProductsToCategoryRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'product_ids' => 'required|array',
            'product_ids.*' => 'required|exists:products,id,deleted_at,NULL',
        ];
    }
} 