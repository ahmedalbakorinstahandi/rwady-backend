<?php


namespace App\Http\Services;

use App\Models\Country;
use App\Services\FilterService;
use App\Services\MessageService;

class CountryService
{
    public function index($filters = [])
    {
        $query = Country::query();

        $query = FilterService::applyFilters(
            $query,
            $filters,
            ['name'],
            [],
            ['created_at'],
            ['place_id', 'id'],
            ['id']
        );

        return $query;
    }

    public function show($id)
    {
        $country = Country::where('id', $id)->first();
        
        if (!$country) {
            return MessageService::abort(404, 'messages.country.not_found');
        }

        return $country;
    }

    public function create($data)
    {
        $country = Country::create($data);
        return $country;
    }
    
    public function update($country, $data)
    {
        $country->update($data);
        return $country;
    }
    
    public function delete($country)
    {
        $country->delete();
        return $country;
    }
    
    
}
