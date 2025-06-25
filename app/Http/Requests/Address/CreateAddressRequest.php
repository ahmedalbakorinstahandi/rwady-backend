<?php

namespace App\Http\Requests\Address;

use App\Http\Requests\BaseFormRequest;

class CreateAddressRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            // 'address' => 'required|string|max:255',
            'exstra_address' => 'nullable|string|max:255',
            // 'country' => 'required|string|max:255',
            // 'city' => 'required|string|max:255',
            // 'state' => 'nullable|string|max:255',
            // 'zipe_code' => 'nullable|string|max:255',
            'longitude' => 'required|numeric',
            'latitude' => 'required|string',
            'is_default' => 'nullable|boolean',
        ];
    }
} 