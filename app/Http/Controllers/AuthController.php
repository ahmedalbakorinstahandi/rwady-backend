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
            'message' => 'messages.otp_verified',
            'data' => $data,
            'status' => 200,
        ]);
    }

    public function logout()
    {

        $token = request()->bearerToken();

        $this->authService->logout($token);

        return ResponseService::response([
            'status' => 200,
            'message' => 'messages.user_logged_out_successfully',
        ]);
    }

    public function requestDeleteAccount()
    {
        $user = $this->authService->requestDeleteAccount();

        return ResponseService::response([
            'status' => 200,
            'message' => 'messages.user.delete_account_code_sent',
            'data' => $user,
        ]);
    }

    public function confirmDeleteAccount()
    {
        $user = $this->authService->confirmDeleteAccount();

        return ResponseService::response([
            'status' => 200,
            'message' => 'messages.user.account_deleted_successfully',
            'data' => $user,
        ]);
    }
}
