<?php

namespace App\Http\Requests\Address;

use App\Http\Requests\BaseFormRequest;

class UpdateAddressRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'exstra_adress' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'zipe_code' => 'nullable|string|max:255',
        ];
    }
} 