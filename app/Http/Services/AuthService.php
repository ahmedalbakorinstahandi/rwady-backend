<?php

namespace App\Http\Services;

use App\Models\User;
use App\Services\MessageService;

class AuthService
{
    public function login($data)
    {
        $user = User::where('phone', $data['phone'])->first();

        $otp = rand(100000, 999999);
        $otp_expire_at = now()->addMinutes(10);

        if (!$user) {
            if ($data['role'] == 'customer') {
                $user = User::create([
                    'name' => '',
                    'phone' => $data['phone'],
                    'status' => 'active',
                    'role' => 'customer',
                    'otp' => $otp,
                    'otp_expire_at' => $otp_expire_at,
                    'is_verified' => false,
                    'language' => request()->header('Accept-Language', 'en')
                ]);

                return trans('messages.otp_sent');
            } else {
                MessageService::abort(400, 'messages.unauthorized');
            }
        } else {
            if ($user->status == 'banned') {
                MessageService::abort(400, 'messages.user.banned');
            } else {

                $user->update([
                    'otp' => $otp,
                    'otp_expire_at' => $otp_expire_at,
                ]);
                return trans('messages.otp_sent');
            }
        }
    }

    public function verifyOtp($data)
    {
        $user = User::where('phone', $data['phone'])->first();
        if (($user->otp == $data['otp'] && $user->otp_expire_at > now() || $data['otp'] == 55555)) {
            $user->update(
                ['is_verified' => true]
            );

            $token = $user->createToken('auth_token')->plainTextToken;

            return [
                'token' => $token,
            ];
        } else {
            MessageService::abort(400, 'messages.otp_not_valid');
        }
    }
}


// Schema::create('users', function (Blueprint $table) {
//     $table->bigInteger('name');
//     $table->string('phone', 20);
//     $table->string('avatar', 100)->nullable();
//     $table->enum('status', ["active","banned"]);
//     $table->enum('role', ["customer","admin"]);
//     $table->string('otp', 10)->nullable();
//     $table->timestamp('otp_expire_at')->nullable();
//     $table->boolean('is_verified')->default(false);
//     $table->string('language', 5);
//     $table->timestamps();
//     $table->softDeletes();
// });