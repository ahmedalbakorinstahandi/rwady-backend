<?php

namespace App\Http\Requests\Product;

use App\Http\Requests\BaseFormRequest;
use App\Models\Category;
use App\Services\LanguageService;

class ReOrderProductRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'orders' => 'integer|required|exists:products,id',
        ];
    }
}
