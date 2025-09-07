<?php

namespace App\Http\Requests\Address;

use App\Http\Requests\BaseFormRequest;

class UpdateAddressRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'nullable|string|max:255',
            'phone' => 'nullable|phone',
            'address' => 'nullable|string|max:255',
            'exstra_address' => 'nullable|string|max:255',
            'country' => 'nullable|exists:countries,id,deleted_at,NULL',
            'city' => 'nullable|exists:cities,id,deleted_at,NULL',
            'is_default' => 'nullable|boolean',
        ];
    }
}
