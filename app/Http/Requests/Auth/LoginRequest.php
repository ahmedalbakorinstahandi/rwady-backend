<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseFormRequest;

class LoginRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'phone' => 'required|string|max:20',
            'role' => 'required|string|in:customer,admin',
        ];
    }
}
