<?php

namespace App\Http\Controllers;

use App\Http\Requests\Brand\CreateBrandRequest;
use App\Http\Requests\Brand\UpdateBrandRequest;
use App\Http\Resources\BrandResource;
use App\Http\Services\BrandService;
use App\Services\ResponseService;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    protected $brandService;

    public function __construct(BrandService $brandService)
    {
        $this->brandService = $brandService;
    }

    public function index(Request $request)
    {
        $brands = $this->brandService->index($request->all());

        return ResponseService::response([
            'success' => true,
            'data' => $brands,
            'meta' => true,
            'resource' => BrandResource::class,
            'status' => 200,
        ]);
    }

    public function show(int $id)
    {
        $brand = $this->brandService->show($id);

        return ResponseService::response([
            'success' => true,
            'data' => $brand,
            'resource' => BrandResource::class,
            'status' => 200,
        ]);
    }

    public function create(CreateBrandRequest $request)
    {
        $brand = $this->brandService->create($request->validated());

        return ResponseService::response([
            'success' => true,
            'data' => $brand,
            'message' => 'messages.brand.created_successfully',
            'status' => 201,
            'resource' => BrandResource::class,
        ]);
    }

    public function update(UpdateBrandRequest $request, int $id)
    {
        $brand = $this->brandService->show($id);
        $brand = $this->brandService->update($request->validated(), $brand);

        return ResponseService::response([
            'success' => true,
            'data' => $brand,
            'message' => 'messages.brand.updated_successfully',
            'status' => 200,
            'resource' => BrandResource::class,
        ]);
    }

    public function delete(int $id)
    {
        $brand = $this->brandService->show($id);
        $this->brandService->delete($brand);

        return ResponseService::response([
            'success' => true,
            'message' => 'messages.brand.deleted_successfully',
            'status' => 200,
        ]);
    }
} 