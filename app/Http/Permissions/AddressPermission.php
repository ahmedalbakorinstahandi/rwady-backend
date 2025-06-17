<?php

namespace App\Http\Permissions;

use App\Models\User;
use App\Services\MessageService;

class AddressPermission
{
    public static function filterIndex($query)
    {
        $user = User::auth();

        if ($user && !$user->isAdmin()) {
            $query->where('addressable_id', $user->id);
            $query->where('addressable_type', User::class);
        }

        return $query;
    }


    // show
    public static function canShow($address)
    {
        $user = User::auth();

        if ($user && !$user->isAdmin()) {
            if ($address->addressable_id != $user->id) {
                MessageService::abort(403, 'message.permission.error');
            }
        }

        return $address;
    }

    // create
    public static function create($data)
    {
        $authorizedUser = User::auth();

        if ($authorizedUser->isCustomer()) {
            $data['addressable_id'] = $authorizedUser->id;
        } else {
            $data['addressable_id'] = $data['user_id'];
        }

        $data['addressable_type'] = User::class;

        $user = User::find($data['user_id']);

        $defaultAddress = $user->addresses()->where('is_default', true)->first();

        if (!$defaultAddress) {
            $data['is_default'] = true;
        } else {
            if ($data['is_default'] == true) {
                $user->addresses()->where('is_default', true)->update(['is_default' => false]);
                $data['is_default'] = true;
            }
        }





        return $data;
    }

    // update
    public static function canUpdate($address, $data)
    {
        $user = User::auth();

        if ($user && !$user->isAdmin()) {
            if ($address->addressable_id != $user->id) {
                MessageService::abort(403, 'message.permission.error');
            }
        }

        return $data;
    }

    // delete
    public static function canDelete($address)
    {
        $user = User::auth();
    }
}
