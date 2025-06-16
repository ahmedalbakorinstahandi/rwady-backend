<?php

namespace App\Http\Requests\FeaturedSection;

use App\Http\Requests\BaseFormRequest;
use App\Services\LanguageService;

class UpdateFeaturedSectionRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'name' => LanguageService::translatableFieldRules('nullable|string|max:255'),
            'image' => 'nullable|string|max:100',
            'link' => 'nullable|string|url',
            'start_date' => 'nullable|date_format:Y-m-d H:i:s',
            'end_date' => 'nullable|date_format:Y-m-d H:i:s|after:start_date',
            'availability' => 'nullable|boolean',
        ];
    }
} 