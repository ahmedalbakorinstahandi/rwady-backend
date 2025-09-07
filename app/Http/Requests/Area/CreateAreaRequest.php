<?php

namespace App\Http\Requests\Area;

use App\Http\Requests\BaseFormRequest;
use App\Services\LanguageService;

class CreateAreaRequest extends BaseFormRequest
{

    


    public function rules(): array
    {
        return [
            'name' => LanguageService::translatableFieldRules('required|string|max:255'),
            'place_id' => 'nullable|string|max:255',
            'city_id' => 'exists:cities,id,deleted_at,NULL|required',
        ];
    }
}
