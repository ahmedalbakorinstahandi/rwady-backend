<?php

namespace App\Http\Requests\Notification;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Foundation\Http\FormRequest;

class CreateRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'user_id'               => 'required|exists:users,id',
            'title'                 => 'required|string|max:255',
            'message'               => 'nullable|string',
            'notificationable_id'   => 'nullable|integer',
            'notificationable_type' => 'nullable|string|max:255',
            'metadata'              => 'nullable|array',
            'read_at'               => 'nullable|date',
        ];
    }
}