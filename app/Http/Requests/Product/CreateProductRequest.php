<?php

namespace App\Http\Requests\Product;

use App\Http\Requests\BaseFormRequest;
use App\Services\LanguageService;

class CreateProductRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'sku' => 'nullable|string|max:255',
            'name' => LanguageService::translatableFieldRules('required|string|max:255'),
            'description' => LanguageService::translatableFieldRules('nullable|string'),
            'ribbon_text' => LanguageService::translatableFieldRules('nullable|string|max:15'),
            'ribbon_color' => [
                'nullable',
                'string',
                function ($attribute, $value, $fail) {
                    if (!is_null($value) && !is_string($value)) {
                        $fail('The ' . $attribute . ' must be a string.');
                    }
                    if (!is_null($value) && !preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $value)) {
                        $fail('The ' . $attribute . ' must be a valid hex color.');
                    }
                }
            ],
            'is_recommended' => 'nullable|boolean',
            'price' => 'nullable|numeric|min:0',
            'price_after_discount' => 'nullable|numeric|min:0',
            'price_discount_start' => 'nullable|date',
            'price_discount_end' => 'nullable|date',
            'cost_price' => 'nullable|numeric|min:0',
            'cost_price_after_discount' => 'nullable|numeric|min:0',
            'cost_price_discount_start' => 'nullable|date',
            'cost_price_discount_end' => 'nullable|date',
            'availability' => 'nullable|boolean',
            'stock' => 'nullable|integer|min:0',
            'stock_unlimited' => 'nullable|boolean',
            'out_of_stock' => 'nullable|string|in:show_on_storefront,hide_from_storefront,show_and_allow_pre_order',
            'minimum_purchase' => 'nullable|integer|min:0',
            'maximum_purchase' => 'nullable|integer|min:0',
            'weight' => 'nullable|numeric|min:0',
            'length' => 'nullable|numeric|min:0',
            'width' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'shipping_type' => 'nullable|string|in:default,fixed_shipping,free_shipping',
            'shipping_rate_single' => 'nullable|numeric|min:0',
            'shipping_rate_multi' => 'nullable|numeric|min:0',

            'categories' => 'nullable|array',
            'categories.*' => 'required|exists:categories,id,deleted_at,NULL',

            'brands' => 'nullable|array',
            'brands.*' => 'required|exists:brands,id,deleted_at,NULL',

            'colors' => 'nullable|array',
            'colors.*' => [
                'nullable',
                'string',
                function ($attribute, $value, $fail) {
                    if (!is_null($value) && !is_string($value)) {
                        $fail('The ' . $attribute . ' must be a string.');
                    }
                    if (!is_null($value) && !preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $value)) {
                        $fail('The ' . $attribute . ' must be a valid hex color.');
                    }
                }
            ],

            'images' => 'nullable|array',
            'images.*.path' => 'required|string|max:500',

            'videos' => 'nullable|array',
            'videos.*.link' => 'required|string|max:500',

            'seo' => 'nullable|array',
            'seo.meta_title' => LanguageService::translatableFieldRules('nullable|string|max:255'),
            'seo.meta_description' => LanguageService::translatableFieldRules('nullable|string'),
            'seo.keywords' =>  'nullable|string|max:255',
            'seo.image' => 'nullable|string|max:100',

            'related_category_id' => 'nullable|exists:categories,id,deleted_at,NULL',
            'related_products' => 'nullable|array',
            'related_products.*' => 'required|exists:products,id,deleted_at,NULL',
        ];
    }
}
