<?php

namespace App\Http\Controllers;

use App\Http\Requests\Settings\CreateSettingRequest;
use App\Http\Requests\Settings\UpdateSettingRequest;
use App\Http\Requests\Settings\UpdateSettingsRequest;
use App\Http\Resources\SettingResource;
use App\Http\Services\SettingService;
use App\Services\ResponseService;

class SettingController extends Controller
{

    protected $settingService;
    public function __construct(SettingService $settingService)
    {
        $this->settingService = $settingService;
    }


    public function index()
    {
        $settings = $this->settingService->index(request()->all());

        return response()->json([
            'success' => true,
            'data' => SettingResource::collection($settings->items()),
            'meta' => ResponseService::meta($settings),
        ]);
    }

    public function show($id)
    {
        $setting = $this->settingService->show($id);

        return response()->json([
            'success' => true,
            'data' => new SettingResource($setting),
        ]);
    }

    public function create(CreateSettingRequest $request)
    {
        $setting = $this->settingService->create($request->validated());

        return response()->json([
            'success' => true,
            'data' => new SettingResource($setting),
        ]);
    }

    public function updateMany(UpdateSettingsRequest $request)
    {
        $this->settingService->updateMany($request->validated()['settings']);



        return response()->json([
            'success' => true,
            'data' => [],
            'message' => trans('messages.setting.item_updated_successfully'),
        ]);
    }

    public function updateOne($idOrKey, UpdateSettingRequest $request)
    {
        $setting = $this->settingService->updateOne($idOrKey, $request->validated());

        return response()->json([
            'success' => true,
            'data' => new SettingResource($setting),
        ]);
    }



    public function delete($idOrKey)
    {

        $setting = $this->settingService->show($idOrKey);

        $this->settingService->delete($setting);

        return response()->json([
            'success' => true,
            'message' => trans('messages.setting.item_deleted_successfully'),
        ]);
    }
}
