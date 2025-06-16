<?php

namespace App\Http\Requests\User;

use App\Http\Requests\BaseFormRequest;

class UpdateMyDataRequest extends BaseFormRequest
{

    public function rules(): array
    {
        return [
            'name' => 'nullable|string|max:255',
            'avatar' => 'nullable|string|max:255',
            'language' => 'nullable|string|max:5',
        ];
    }
}