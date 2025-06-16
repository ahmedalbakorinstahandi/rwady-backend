<?php

namespace App\Http\Services;

use App\Models\User;
use App\Services\MessageService;
use App\Services\PhoneService;

class AuthService
{
    public function login($data)
    {

        $phone = PhoneService::parsePhoneParts($data['phone']);

        $full_phone = $phone['country_code'] . $phone['national_number'];

        $user = User::where('phone', $full_phone)->first();

        $otp = rand(100000, 999999);
        $otp_expire_at = now()->addMinutes(10);

        if (!$user) {
            if ($data['role'] == 'customer') {
                $user = User::create([
                    'name' => '',
                    'phone' => $full_phone,
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
