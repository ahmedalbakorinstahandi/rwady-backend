<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\UserResource;

class NotificationResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'               => $this->id,
            'user_id'          => $this->user_id,
            'title'            => $this->title,
            'message'          => $this->message,
            'notificationable_id' => $this->notificationable_id,
            'notificationable_type' => $this->notificationable_type,
            'notificationable' => $this->whenLoaded('notificationable'),
            'read_at'          => $this->read_at?->format('Y-m-d H:i:s'),
            'is_read'          => $this->is_read || $this->user_id == null,
            'metadata'         => $this->metadata,

            'user'             => new UserResource($this->whenLoaded('user')),

            'created_at'       => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at'       => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
