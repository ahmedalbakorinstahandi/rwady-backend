<?php

namespace App\Http\Requests\Settings;

use App\Http\Requests\BaseFormRequest;

class UpdateSettingRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'value' => 'required|string',
        ];
    }
}
