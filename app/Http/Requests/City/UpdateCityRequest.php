<?php

namespace App\Http\Requests\City;

use App\Http\Requests\BaseFormRequest;
use App\Services\LanguageService;

class UpdateCityRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'name' => LanguageService::translatableFieldRules('nullable|string|max:255'),
            'place_id' => 'nullable|string|max:255',
            'country_id' => 'nullable|exists:countries,id,deleted_at,NULL',
        ];
    }
}
