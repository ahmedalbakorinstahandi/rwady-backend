<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CountryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            'name'      => $this->name,
            'place_id'  => $this->place_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'cities'    => CityResource::collection($this->whenLoaded('cities')),
        ];
    }
}
