<?php

namespace App\Http\Requests\Brand;

use App\Http\Requests\BaseFormRequest;
use App\Services\LanguageService;

class CreateBrandRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'name' => LanguageService::translatableFieldRules('required|string|max:255'),
            'image' => 'nullable|string|max:255',
            'availability' => 'nullable|boolean',
            'seo' => 'nullable|array',
            'seo.meta_title' => 'nullable|string|max:255',
            'seo.meta_description' => 'nullable|string',
            'seo.keywords' => 'nullable|string|max:255',
            'seo.image' => 'nullable|string|max:255',
        ];
    }
}
