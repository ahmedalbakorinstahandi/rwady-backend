<?php

namespace App\Http\Requests\Category;

use App\Http\Requests\BaseFormRequest;
use App\Services\LanguageService;

class CreateCategoryRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'name' => LanguageService::translatableFieldRules('required|string|max:255'),
            'description' => LanguageService::translatableFieldRules('nullable|string'),
            'parent_id' => 'nullable|exists:categories,id,deleted_at,NULL',
            'image' => 'nullable|string|max:255',
        ];
    }
} 