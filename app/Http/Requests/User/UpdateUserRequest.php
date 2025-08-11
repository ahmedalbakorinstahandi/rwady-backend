<?php

namespace App\Http\Requests\User;

use App\Http\Requests\BaseFormRequest;

class UpdateUserRequest extends BaseFormRequest
{

    public function rules(): array
    {
        return [
            'name' => 'nullable|string|max:255',
            'phone' => 'nullable|phone',
            'avatar' => 'nullable|string|max:100',
            'status' => 'nullable|in:active,banned',
        ];
    }
}