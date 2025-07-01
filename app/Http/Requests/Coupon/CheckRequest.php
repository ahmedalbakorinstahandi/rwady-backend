<?php

namespace App\Http\Requests\Coupon;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Foundation\Http\FormRequest;

class CheckRequest extends BaseFormRequest
{

    public function rules(): array
    {
        return [
            'coupon' => 'required|string|max:50',
        ];
    }
}
