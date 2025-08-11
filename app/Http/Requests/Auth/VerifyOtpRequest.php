<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseFormRequest;
use App\Rules\PhoneValidation;

class VerifyOtpRequest extends BaseFormRequest
{

    public function rules(): array
    {
        return [
            'phone' => ['required', new PhoneValidation],
            'otp' => 'required|string|max:6',
        ];
    }
}
