<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Http\Services\AuthService;
use App\Services\ResponseService;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(LoginRequest $request)
    {
        $data = $request->validated();

        $message =  $this->authService->login($data);

        return ResponseService::response([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'status' => 200,
        ]);
    }

    public function verifyOtp(VerifyOtpRequest $request)
    {
        $data = $this->authService->verifyOtp($request->validated());

        return ResponseService::response([
            'success' => true,
            'message' => trans('messages.otp_verified'),
            'data' => $data,
            'status' => 200,
        ]);
    }
}
