<?php

namespace App\Http\Requests\Category;

use App\Http\Requests\BaseFormRequest;

class ReOrderCategoryRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'orders' => 'integer|required|exists:categories,id',
        ];
    }
}
