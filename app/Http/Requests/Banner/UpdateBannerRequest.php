<?php

namespace App\Http\Requests\Banner;

use App\Http\Requests\BaseFormRequest;
use App\Services\LanguageService;

class UpdateBannerRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'title' => LanguageService::translatableFieldRules('nullable|string|max:255'),
            'description' => LanguageService::translatableFieldRules('nullable|string'),
            'button_text' => LanguageService::translatableFieldRules('nullable|string|max:255'),
            'image' => 'nullable|string|max:100',
            'is_popup' => 'nullable|boolean',
            'link' => 'nullable|string|url',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'availability' => 'nullable|boolean',
        ];
    }
} 