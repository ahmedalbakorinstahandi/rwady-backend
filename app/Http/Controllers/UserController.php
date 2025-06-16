<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\UpdateMyDataRequest;
use App\Http\Services\UserService;
use App\Services\ResponseService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function getMyData()
    {
        $data = $this->userService->getMyData();

        return ResponseService::response([
            'success' => true,
            'data' => $data,
            'status' => 200,
        ]);
    }

    public function updateMyData(UpdateMyDataRequest $request)
    {
        $data = $this->userService->updateMyData($request->validated());

        return ResponseService::response([
            'success' => true,
            'data' => $data,
            'status' => 200,
        ]);
    }
}
