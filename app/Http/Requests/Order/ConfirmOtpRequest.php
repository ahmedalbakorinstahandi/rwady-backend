<?php

namespace App\Http\Requests\Order;

use App\Http\Requests\BaseFormRequest;

class ConfirmOtpRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'otp' => 'required|numeric',
        ];
    }
}
