<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseFormRequest;
use App\Rules\PhoneValidation;

class LoginRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'phone' => ['required', new PhoneValidation],
            'role' => 'required|string|in:customer,admin',
        ];
    }
}
