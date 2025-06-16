<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'model_type' => $this->model_type,
            'model_id' => $this->model_id,
            'file_name' => $this->file_name,
            'file_path' => $this->file_path,
            'file_type' => $this->file_type,
            'file_size' => $this->file_size,
            'alt_text' => $this->alt_text,
            'title' => $this->title,
            'order' => $this->order,
            'model' => $this->whenLoaded('model'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
} 