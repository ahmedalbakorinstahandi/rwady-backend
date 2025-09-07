<?php

namespace App\Http\Controllers;

use App\Http\Requests\Country\CreateCountryRequest;
use App\Http\Requests\Country\UpdateCountryRequest;
use App\Http\Resources\CountryResource;
use App\Http\Services\CountryService;
use App\Services\ResponseService;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    protected $countryService;

    public function __construct(CountryService $countryService)
    {
        $this->countryService = $countryService;
    }

    public function index(Request $request)
    {
        $countries = $this->countryService->index($request->all());

        return ResponseService::response([
            'success' => true,
            'data' => $countries,
            'meta' => true,
            'resource' => CountryResource::class,
            'status' => 200,
        ]);
    }


    public function show($id)
    {
        $country = $this->countryService->show($id);

        return ResponseService::response([
            'success' => true,
            'data' => $country,
            'resource' => CountryResource::class,
            'status' => 200,
        ]);
    }


    public function create(CreateCountryRequest $request)
    {
        $country = $this->countryService->create($request->validated());

        return ResponseService::response([
            'success' => true,
            'data' => $country,
            'resource' => CountryResource::class,
            'status' => 200,
        ]);
    }

    public function update(UpdateCountryRequest $request, $id)
    {
        $country = $this->countryService->show($id);


        $country = $this->countryService->update($country, $request->validated());

        return ResponseService::response([
            'success' => true,
            'data' => $country,
            'resource' => CountryResource::class,
            'status' => 200,
        ]);
    }


    public function delete($id)
    {
        $country = $this->countryService->show($id);

        $country = $this->countryService->delete($country);

        return ResponseService::response([
            'success' => true,
            'data' => $country,
            'resource' => CountryResource::class,
            'status' => 200,
        ]);
    }
}
