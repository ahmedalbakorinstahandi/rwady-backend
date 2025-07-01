<?php

namespace App\Http\Requests\Settings;

use App\Http\Requests\BaseFormRequest;

class CreateSettingRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'key' => 'required|string',
            'value' => 'required|string',
            'type' => 'required|in:int,float,text,long_text,list,json,image,file,bool,time,date,datetime,html',
            'allow_null' => 'required|boolean',
        ];
    }
}
