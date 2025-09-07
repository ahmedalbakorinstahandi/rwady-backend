<?php

namespace App\Http\Requests\Country;

use App\Http\Requests\BaseFormRequest;
use App\Services\LanguageService;

class UpdateCountryRequest extends BaseFormRequest
{

    public function rules(): array
    {
        return [
            'name' => LanguageService::translatableFieldRules('nullable|string|max:255'),
            'place_id' => 'nullable|string|max:255',
        ];
    }
}
