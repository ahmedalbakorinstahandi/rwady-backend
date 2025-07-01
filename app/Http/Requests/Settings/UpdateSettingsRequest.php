<?php

namespace App\Http\Requests\Settings;

use App\Http\Requests\BaseFormRequest;

class UpdateSettingsRequest extends BaseFormRequest
{
    public function rules(): array
    {
        // i need array have key and value
        return [
            'settings' => 'required|array',
            'settings.*.key' => 'required|string',
            'settings.*.value' => 'required|string',
        ];
    }
}
