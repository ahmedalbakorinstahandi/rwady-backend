<?php

namespace App\Http\Resources;

use App\Http\Resources\CityResource;
use App\Http\Resources\CountryResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AddressResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'address' => $this->address,
            'extra_address' => $this->exstra_address,
            'country' => new CountryResource($this->whenLoaded('country')),
            'city' => new CityResource($this->whenLoaded('city')),
            'state' => $this->state ?? null,
            'zipe_code' => $this->zipe_code ?? null,
            'longitude' => $this->longitude ?? null,
            'latitude' => $this->latitude ?? null,
            'addressable_id' => $this->addressable_id,
            'addressable_type' => $this->addressable_type,
            'is_default' => $this->is_default,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
