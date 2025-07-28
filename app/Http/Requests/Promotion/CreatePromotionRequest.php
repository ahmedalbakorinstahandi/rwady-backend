<?php

namespace App\Http\Requests\Promotion;

use App\Http\Requests\BaseFormRequest;
use App\Services\LanguageService;
use Faker\Provider\Base;
use Illuminate\Foundation\Http\FormRequest;

class CreatePromotionRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'title' => LanguageService::translatableFieldRules('required|string|max:255'),
            'type' => 'required|in:product,category,cart_total,shipping',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0',
            'min_cart_total' => 'required_if:type,cart_total|numeric|min:0',
            'start_at' => 'nullable|date_format:Y-m-d H:i',
            'end_at' => 'nullable|date_format:Y-m-d H:i|after_or_equal:start_at',
            'status' => 'required|in:draft,active,inactive',
            'categories' => 'required_if:type,category|array|min:1',
            'categories.*' => 'exists:categories,id,deleted_at,NULL',
            'products' => 'required_if:type,product|array|min:1',
            'products.*' => 'exists:products,id,deleted_at,NULL',
        ];
    }
}
