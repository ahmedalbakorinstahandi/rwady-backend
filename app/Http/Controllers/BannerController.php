<?php

namespace App\Http\Controllers;

use App\Http\Requests\Banner\CreateBannerRequest;
use App\Http\Requests\Banner\UpdateBannerRequest;
use App\Http\Resources\BannerResource;
use App\Http\Services\BannerService;
use App\Services\ResponseService;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    protected $bannerService;

    public function __construct(BannerService $bannerService)
    {
        $this->bannerService = $bannerService;
    }

    public function index(Request $request)
    {
        $banners = $this->bannerService->index($request->all());

        return ResponseService::response([
            'success' => true,
            'data' => $banners,
            'meta' => true,
            'resource' => BannerResource::class,
            'status' => 200,
        ]);
    }

    public function show(int $id)
    {
        $banner = $this->bannerService->show($id);

        return ResponseService::response([
            'success' => true,
            'data' => $banner,
            'status' => 200,
            'resource' => BannerResource::class,
        ]);
    }

    public function create(CreateBannerRequest $request)
    {
        $banner = $this->bannerService->create($request->validated());

        return ResponseService::response([
            'success' => true,
            'data' => $banner,
            'message' => 'messages.banner.created_successfully',
            'status' => 201,
            'resource' => BannerResource::class,
        ]);
    }

    public function update(UpdateBannerRequest $request, int $id)
    {
        $banner = $this->bannerService->show($id);
        $banner = $this->bannerService->update($request->validated(), $banner);

        return ResponseService::response([
            'success' => true,
            'data' => $banner,
            'message' => 'messages.banner.updated_successfully',
            'status' => 200,
            'resource' => BannerResource::class,
        ]);
    }

    public function delete(int $id)
    {
        $banner = $this->bannerService->show($id);
        $this->bannerService->delete($banner);

        return ResponseService::response([
            'success' => true,
            'message' => 'messages.banner.deleted_successfully',
            'status' => 200,
        ]);
    }
} 