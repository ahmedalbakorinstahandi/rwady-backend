<?php

namespace App\Http\Requests\Promotion;

use App\Http\Requests\BaseFormRequest;
use App\Models\Promotion;
use App\Services\LanguageService;
use App\Services\MessageService;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePromotionRequest extends BaseFormRequest
{
    public function rules(): array
    {

        return [
            'title' => LanguageService::translatableFieldRules('nullable|string|max:255'),
            'type' => 'required|in:product,category,cart_total,shipping',
            'discount_type' => 'nullable|in:percentage,fixed',
            'discount_value' => 'nullable|numeric|min:0',
            'min_cart_total' => 'required_if:type,cart_total|numeric|min:0',
            'start_at' => 'nullable|date',
            'end_at' => 'nullable|date|after_or_equal:start_at',
            'status' => 'nullable|in:draft,active,inactive',
            'categories' => 'required_if:type,category|array|min:1',
            'categories.*' => 'exists:categories,id,deleted_at,NULL',
            'products' => 'required_if:type,product|array|min:1',
            'products.*' => 'exists:products,id,deleted_at,NULL',
        ];
    }
}
