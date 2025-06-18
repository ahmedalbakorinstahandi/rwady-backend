<?php

namespace App\Http\Requests\HomeSection;

use App\Http\Requests\BaseFormRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Services\LanguageService;
use Illuminate\Foundation\Http\FormRequest;

class CreateHomeSectionRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'title' => LanguageService::translatableFieldRules('required|string|max:255'),
            'type'=> 'required|in:category_products,brand_products',
            'item_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    $type = request('type');
                    if ($type === 'category_products') {
                        if (!is_numeric($value) || !Category::where('id', $value)->exists()) {
                            $fail('The selected item_id must be a valid category ID.');
                        }
                    } elseif ($type === 'brand_products') {
                        if (!is_numeric($value) || !Brand::where('id', $value)->exists()) {
                            $fail('The selected item_id must be a valid brand ID.');
                        }
                    }
                }
            ],
            'limit' => 'required|integer|min:1',
            'availability' => 'nullable|boolean',

        ];
    }
}
 
 