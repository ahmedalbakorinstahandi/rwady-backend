<?php

namespace App\Http\Services;

use App\Models\User;

class UserService
{
    public function getMyData()
    {

        $user = User::auth();



        return $user;
    }

    public function updateMyData($data)
    {

        $user = User::auth();

        $user->update($data);

        return $user;
    }
}
