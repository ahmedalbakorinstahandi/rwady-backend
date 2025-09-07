<?php

namespace App\Http\Controllers;

use App\Http\Requests\City\CreateCityRequest;
use App\Http\Requests\City\UpdateCityRequest;
use App\Http\Resources\CityResource;
use App\Http\Services\CityService;
use App\Services\ResponseService;
use Illuminate\Http\Request;

class CityController extends Controller
{
    protected $cityService;

    public function __construct(CityService $cityService)
    {
        $this->cityService = $cityService;
    }

    public function index(Request $request)
    {
        $cities = $this->cityService->index($request->all());

        return ResponseService::response([
            'success' => true,
            'data' => $cities,
            'meta' => true,
            'resource' => CityResource::class,
            'status' => 200,
        ]);
    }

    public function show($id)
    {
        $city = $this->cityService->show($id);

        return ResponseService::response([
            'success' => true,
            'data' => $city,
            'resource' => CityResource::class,
            'status' => 200,
        ]);
    }

    public function create(CreateCityRequest $request)
    {
        $city = $this->cityService->create($request->validated());

        return ResponseService::response([
            'success' => true,
            'data' => $city,
            'resource' => CityResource::class,
            'status' => 200,
        ]);
    }

    public function update(UpdateCityRequest $request, $id)
    {
        $city = $this->cityService->show($id);
        $city = $this->cityService->update($request->validated(), $city);

        return ResponseService::response([
            'success' => true,
            'data' => $city,
            'resource' => CityResource::class,
            'status' => 200,
        ]);
    }

    public function delete($id)
    {
        $city = $this->cityService->show($id);
        $city = $this->cityService->delete($city);

        return ResponseService::response([
            'success' => true,
            'data' => $city,
            'resource' => CityResource::class,
            'status' => 200,
        ]);
    }
}
