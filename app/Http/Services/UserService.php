<?php

namespace App\Http\Services;

use App\Models\User;

class UserService
{
    public function getMyData()
    {

        $user = User::auth();

        $user->load('addresses');


        return $user;
    }

    public function updateMyData($data)
    {

        $user = User::auth();

        $user->update($data);

        $user->load('addresses');

        return $user;
    }
}
