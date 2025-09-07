<?php

namespace App\Http\Services;

use App\Models\Area;
use App\Services\FilterService;
use App\Services\MessageService;

class AreaService
{

    public function index($filters = [])
    {
        $query = Area::query()->with('city');

        $query = FilterService::applyFilters(
            $query,
            $filters,
            ['name'],
            [],
            ['created_at'],
            ['place_id', 'id', 'city_id'],
            ['id']
        );

        return $query;
    }

    public function show($id)
    {
        $area = Area::where('id', $id)->first();
        if (!$area) {
            MessageService::abort(404, 'messages.area.not_found');
        }

        $area->load('city');

        return $area;
    }

    public function create($data)
    {
        $area = Area::create($data);
        $area->load('city');
        return $area;
    }


    public function update($area, $data)
    {
        $area->update($data);
        $area->load('city');
        return $area;
    }


    public function delete($area)
    {
        $area->delete();
        $area->load('city');
        return $area;
    }
}
