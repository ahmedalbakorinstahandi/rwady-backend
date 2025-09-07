<?php

namespace App\Http\Requests\City;

use App\Http\Requests\BaseFormRequest;
use App\Services\LanguageService;

class CreateCityRequest extends BaseFormRequest
{

    public function rules(): array
    {
        return [
            'name' => LanguageService::translatableFieldRules('required|string|max:255'),
            'place_id' => 'nullable|string|max:255',
            'country_id' => 'required|exists:countries,id,deleted_at,NULL',
        ];
    }
}
