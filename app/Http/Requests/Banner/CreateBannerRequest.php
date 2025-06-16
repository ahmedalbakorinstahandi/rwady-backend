<?php

namespace App\Http\Requests\Banner;

use App\Http\Requests\BaseFormRequest;
use App\Services\LanguageService;

class CreateBannerRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'title' => LanguageService::translatableFieldRules('required|string|max:255'),
            'description' => LanguageService::translatableFieldRules('required|string'),
            'button_text' => LanguageService::translatableFieldRules('required|string|max:255'),
            'image' => 'required|string|max:100',
            'is_popup' => 'required|boolean',
            'link' => 'required|string|url',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'availability' => 'nullable|boolean',
        ];
    }
}
