<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseFormRequest;

class LoginRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
           'phone' => 'required|phone',
            'role' => 'required|string|in:customer,admin',
        ];
    }
}
