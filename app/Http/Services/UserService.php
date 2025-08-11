<?php

namespace App\Http\Services;

use App\Models\User;
use App\Services\FilterService;
use App\Services\MessageService;
use App\Services\PhoneService;

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


    public function index($filters = [])
    {
        $query = User::query();

        return FilterService::applyFilters(
            $query,
            $filters,
            ['name', 'phone'], // text fields
            [], // number fields
            ['created_at'], // date fields
            ['status', 'id', 'role'], // match fields
            ['status', 'role', 'language'] // enum fields
        );
    }

    public function show($id)
    {
        $user = User::where('id', $id)->first();

        if (!$user) {
            MessageService::abort(404, 'messages.user.not_found');
        }

        return $user;
    }

    public function create($data)
    {

        $phone = PhoneService::parsePhoneParts($data['phone']);

        $full_phone = $phone['country_code'] . $phone['national_number'];

        $data['phone'] = $full_phone;

        $data['status'] = 'active';
        $data['is_verified'] = true;

        $user = User::create($data);

        return $user;
    }

    public function update($user, $data)
    {
        if (isset($data['phone'])) {
            $phone = PhoneService::parsePhoneParts($data['phone']);
            $full_phone = $phone['country_code'] . $phone['national_number'];
            $data['phone'] = $full_phone;
        }

        $user->update($data);

        return $user;
    }

    public function delete($user)

    {
        $user->delete();

        return $user;
    }
}
