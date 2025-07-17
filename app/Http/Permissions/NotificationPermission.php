<?php


namespace App\Http\Permissions;

use App\Models\Notification;
use App\Models\User;
use App\Services\MessageService;
use Illuminate\Support\Facades\Auth;

class NotificationPermission
{
    public static function filterIndex($query)
    {
        if (Auth::check()) {
            $user = User::auth();

            if ($user->isCustomer()) {
                $query->where('user_id', $user->id)->orWhereNull('user_id');
            } else {
                $query->where('user_id', $user->id);
            }
        } else {
            $query->whereNull('user_id');
        }


        return $query;
    }

    public static function canShow(Notification $notification)
    {
        $user = User::auth();

        if ($notification->user_id !== $user->id) {
            MessageService::abort(403, 'messages.permission_error');
        }

        return true;
    }

    public static function create($data)
    {
        return $data;
    }

    public static function canUpdate(Notification $notification, $data)
    {
        return true;
    }

    public static function canDelete(Notification $notification)
    {
        $user = User::auth();

        if ($notification->user_id !== $user->id) {
            MessageService::abort(403, 'messages.permission_error');
        }

        return true;
    }
}
