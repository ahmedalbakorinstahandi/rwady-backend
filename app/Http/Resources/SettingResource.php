<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SettingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'key' => $this->key,
            'value' => $this->value,
            'type' => $this->type,
            'allow_null' => $this->allow_null,
            'is_setting' => $this->is_setting,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
} 