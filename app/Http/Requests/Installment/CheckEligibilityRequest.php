<?php

namespace App\Http\Requests\Installment;

use App\Http\Requests\BaseFormRequest;

class CheckEligibilityRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'identity' => ['required', 'string', 'min:10'],
        ];
    }
}