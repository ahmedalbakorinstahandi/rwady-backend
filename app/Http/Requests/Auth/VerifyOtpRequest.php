<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseFormRequest;

class VerifyOtpRequest extends BaseFormRequest
{

    public function rules(): array
    {
        return [
           'phone' => 'required|phone',
            'otp' => 'required|string|max:6',
        ];
    }
}
