<?php

namespace App\Http\Notifications;

use App\Models\User;
use App\Services\FirebaseService;

class UserNotification
{
    // new user
    public static function newUser($user)
    {
        $users = User::where('role', 'admin')->get();

        $title = 'notifications.admin.user.new.title';
        $body = 'notifications.admin.user.new.body';

        $replace = [
            'user_id' => $user->id,
            'user_phone' => $user->phone,
        ];

        FirebaseService::sendToTokensAndStorage(
            $users->pluck('id'),
            [
                'id' => $user->id,
                'type' => 'user',
            ],
            $title,
            $body,
            $replace,
            $replace,

        );
    }
}
