<?php

namespace App\Http\Requests\Country;

use App\Http\Requests\BaseFormRequest;
use App\Services\LanguageService;

class CreateCountryRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'name' => LanguageService::translatableFieldRules('required|string|max:255'),
            'place_id' => 'nullable|string|max:255',
        ];
    }
}
