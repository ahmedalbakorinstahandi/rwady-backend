<?php

namespace App\Http\Requests\Area;

use App\Services\LanguageService;
use App\Http\Requests\BaseFormRequest;

class UpdateAreaRequest extends BaseFormRequest
{

    public function rules(): array
    {
        return [
            'name' => LanguageService::translatableFieldRules('nullable|string|max:255'),
            'place_id' => 'nullable|string|max:255',
            'city_id' => 'exists:cities,id,deleted_at,NULL|nullable',
        ];
    }
}
