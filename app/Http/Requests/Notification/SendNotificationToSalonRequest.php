<?php

namespace App\Http\Requests\Notification;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Foundation\Http\FormRequest;

class SendNotificationToSalonRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'title'                 => 'required|string|max:255',
            'message'               => 'required|string',
        ];
    }
}
