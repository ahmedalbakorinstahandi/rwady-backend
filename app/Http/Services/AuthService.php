<?php

namespace App\Http\Services;

use App\Http\Notifications\UserNotification;
use App\Models\User;
use App\Services\BulkSMSIraqService;
use App\Services\MessageService;
use App\Services\PhoneService;
use Laravel\Sanctum\PersonalAccessToken;

class AuthService
{
    public function login($data)
    {

        $locale = explode(',', request()->header('Accept-Language', 'en'))[0];

        if (in_array($locale, ['ar', 'en'])) {
            $language = $locale;
        } else {
            $language = 'ar';
        }

        $phone = PhoneService::parsePhoneParts($data['phone']);

        $full_phone = $phone['country_code'] . $phone['national_number'];

        $user = User::where('phone', $full_phone)->first();

        $otp = rand(10000, 99999);
        $otp_expire_at = now()->addMinutes(5);

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
                    'language' => $language
                ]);

                UserNotification::newUser($user);

                BulkSMSIraqService::send($full_phone, $otp, 'whatsapp', $language);

                return 'messages.otp_sent';
            } else {
                MessageService::abort(400, 'messages.unauthorized');
            }
        } else {
            if ($data['role'] == 'admin' && !$user->isAdmin()) {
                MessageService::abort(400, 'messages.unauthorized');
            }

            if ($user->status == 'banned') {
                MessageService::abort(400, 'messages.user.banned');
            } else {
                $user->update([
                    'otp' => $otp,
                    'otp_expire_at' => $otp_expire_at,
                ]);


                BulkSMSIraqService::send($full_phone, $otp, 'whatsapp', $language);

                return 'messages.otp_sent';
            }
        }
    }

    public function verifyOtp($data)
    {
        $phone = PhoneService::parsePhoneParts($data['phone']);

        $full_phone = $phone['country_code'] . $phone['national_number'];

        $user = User::where('phone', $full_phone)->first();

        if (!$user) {
            MessageService::abort(400, 'messages.user_not_found');
        }

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

    public function logout($token)
    {
        $personalAccessToken = PersonalAccessToken::findToken($token);

        // FirebaseService::unsubscribeFromAllTopic($personalAccessToken->tokenable);

        return $personalAccessToken->delete();
    }
}
