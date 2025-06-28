<?php

namespace App\Http\Requests\Installment;

use App\Http\Requests\BaseFormRequest;

class ValidatePlanRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'session_id' => ['required', 'string'],
            'amount' => ['required', 'numeric', 'min:1000'],
            'count_of_month' => ['required', 'integer', 'min:1', 'max:36'],
        ];
    }
}
