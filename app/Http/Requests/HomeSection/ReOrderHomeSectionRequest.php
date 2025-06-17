<?php

namespace App\Http\Requests\HomeSection;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Foundation\Http\FormRequest;

class ReOrderHomeSectionRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'orders' => 'integer|required|exists:home_sections,id',
        ];
    }
}
