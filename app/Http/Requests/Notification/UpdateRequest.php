<?php

namespace App\Http\Requests\Notification;

use App\Http\Requests\BaseFormRequest;

class UpdateRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'title'                 => 'nullable|string|max:255',
            'message'               => 'nullable|string',
            'notificationable_id'   => 'nullable|integer',
            'notificationable_type' => 'nullable|string|max:255',
            'metadata'              => 'nullable|array',
            'read_at'               => 'nullable|date',
        ];
    }
}
