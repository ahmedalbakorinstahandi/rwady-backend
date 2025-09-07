<?php

namespace App\Http\Requests\Address;

use App\Http\Requests\BaseFormRequest;

class CreateAddressRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'phone' => 'required|phone',
            'country' => 'required|exists:countries,id,deleted_at,NULL',
            'city' => 'required|exists:cities,id,deleted_at,NULL',
            'address' => 'required|string|max:255',
            'exstra_address' => 'nullable|string|max:255',
            'longitude' => 'nullable|numeric',
            'latitude' => 'nullable|string',
            'is_default' => 'nullable|boolean',
        ];
    }
} 