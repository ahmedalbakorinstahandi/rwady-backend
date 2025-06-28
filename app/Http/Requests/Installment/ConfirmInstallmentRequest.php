<?php

namespace App\Http\Requests\Installment;

use App\Http\Requests\BaseFormRequest;

class ConfirmInstallmentRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'session_id' => ['required', 'string'],
            'otp' => ['required', 'numeric'],
            'note' => ['nullable', 'string'],
            'payment_card' => ['nullable', 'string'],
        ];
    }
}