<?php

namespace App\Http\Services;

use App\Models\City;
use App\Services\FilterService;
use App\Services\MessageService;

class CityService
{
    public function index($filters = [])
    {
        $query = City::query();

        $query = FilterService::applyFilters(
            $query,
            $filters,
            ['name'],
            [],
            ['created_at'],
            ['place_id', 'id', 'country_id'],
            ['id']
        );

        return $query;
    }

    public function show($id)
    {
        $city = City::where('id', $id)->first();
        if (!$city) {
            return MessageService::abort(404, 'messages.city.not_found');
        }

        return $city;
    }

    public function create($data)
    {
        $city = City::create($data);

        return $city;
    }
    
    public function update($city, $data)
    {
        $city->update($data);

        return $city;
    }
    
    
    public function delete($city)
    {
        $city->delete();

        return $city;
    }
    
    
}