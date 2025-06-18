<?php

namespace App\Http\Requests\HomeSection;

use App\Http\Requests\BaseFormRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\HomeSection;
use App\Services\LanguageService;
use App\Services\MessageService;
use Illuminate\Foundation\Http\FormRequest;

class UpdateHomeSectionRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'title' => LanguageService::translatableFieldRules('nullable|string|max:255'),
            'limit' => 'nullable|integer|min:1',
            'availability' => 'nullable|boolean',
        ];
    }
}
