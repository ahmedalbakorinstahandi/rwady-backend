<?php

namespace App\Http\Controllers;

use App\Http\Requests\Area\CreateAreaRequest;
use App\Http\Requests\Area\UpdateAreaRequest;
use App\Http\Services\AreaService;
use App\Http\Resources\AreaResource;
use App\Services\ResponseService;
use Illuminate\Http\Request;

class AreaController extends Controller
{

    protected $areaService;

    public function __construct(AreaService $areaService)
    {
        $this->areaService = $areaService;
    }


    public function index(Request $request)
    {
        $areas = $this->areaService->index($request->all());

        return ResponseService::response([
            'success' => true,
            'data' => $areas,
            'meta' => true,
            'resource' => AreaResource::class,
            'status' => 200,
        ]);
    }

    public function show($id)
    {
        $area = $this->areaService->show($id);

        return ResponseService::response([
            'success' => true,
            'data' => $area,
            'resource' => AreaResource::class,
            'status' => 200,
        ]);
    }

    public function create(CreateAreaRequest $request)
    {
        $area = $this->areaService->create($request->validated());

        return ResponseService::response([
            'success' => true,
            'data' => $area,
            'resource' => AreaResource::class,
            'status' => 200,
        ]);
    }

    public function update(UpdateAreaRequest $request, $id)
    {
        $area = $this->areaService->show($id);
        $area = $this->areaService->update($area, $request->validated());

        return ResponseService::response([
            'success' => true,
            'data' => $area,
            'resource' => AreaResource::class,
            'status' => 200,
        ]);
    }

    public function delete($id)
    {
        $area = $this->areaService->show($id);
        $area = $this->areaService->delete($area);

        return ResponseService::response([
            'success' => true,
            'data' => $area,
            'resource' => AreaResource::class,
            'status' => 200,
        ]);
    }
}
