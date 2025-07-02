<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HomeSectionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Cache user auth to avoid repeated queries
        $user = cache()->remember('current_user', 300, function () {
            return User::auth();
        });

        return [
            'id' => $this->id,
            'title' => $this->title,
            'show_title' => $this->show_title,
            'type' => $this->type,
            'item_id' => $this->item_id,
            'status' => $this->status,
            'limit' => $this->limit,
            'can_show_more' => $this->can_show_more,
            'show_more_path' => $this->show_more_path,
            'data' => $this->when(!$user || $user->isCustomer(), $this->getHomeSectionData()),
            // 'data' => $this->when(!$user || $user->isCustomer(), function() {
            //     // Use lazy loading to avoid N+1 queries
            //     return $this->getHomeSectionData();
            // }),
            'orders' => $this->orders,
            'availability' => $this->availability,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
