<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\CreateUserRequest;
use App\Http\Requests\User\UpdateMyDataRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
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

        // name
        // phone
        // address
        // country
        // city
        // lang
        // lat
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

    public function index(Request $request)
    {
        $data = $this->userService->index($request->all());

        return ResponseService::response([
            'success' => true,
            'data' => $data,
            'status' => 200,
            'resource' => UserResource::class,
            'meta' => true,
        ]);
    }

    public function show($id)
    {
        $data = $this->userService->show($id);

        return ResponseService::response([
            'success' => true,
            'data' => $data,
            'status' => 200,
            'resource' => UserResource::class,
        ]);
    }

    public function create(CreateUserRequest $request)
    {
        $data = $this->userService->create($request->validated());

        return ResponseService::response([
            'success' => true,
            'data' => $data,
            'status' => 200,
            'resource' => UserResource::class,
            'message' => 'messages.user.created_successfully',
        ]);
    }

    public function update(UpdateUserRequest $request, $id)
    {
        $user = $this->userService->show($id);

        $data = $this->userService->update($user, $request->validated());

        return ResponseService::response([
            'success' => true,
            'data' => $data,
            'status' => 200,
            'resource' => UserResource::class,
            'message' => 'messages.user.updated_successfully',
        ]);
    }

    public function delete($id)
    {
        $user = $this->userService->show($id);

        $data = $this->userService->delete($user);

        return ResponseService::response([
            'success' => true,
            'data' => $data,
            'status' => 200,
            'message' => 'messages.user.deleted_successfully',
        ]);
    }
}
