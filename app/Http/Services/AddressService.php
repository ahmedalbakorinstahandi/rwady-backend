<?php

namespace App\Http\Services;

use App\Http\Permissions\AddressPermission;
use App\Models\Address;
use App\Services\FilterService;
use App\Services\MessageService;

class AddressService
{
    public function index($filters)
    {
        $query = Address::query();

        $query = AddressPermission::filterIndex($query);

        $searchFields = ['name', 'address', 'exstra_adress', 'country', 'city', 'state', 'zipe_code'];
        $numericFields = ['longitude', 'latitude'];
        $dateFields = [];
        $exactMatchFields = ['is_default'];
        $inFields = [];

        $query = FilterService::applyFilters(
            $query,
            $filters,
            $searchFields,
            $numericFields,
            $dateFields,
            $exactMatchFields,
            $inFields
        );


        return $query;
    }

    public function show($id)
    {
        $address = Address::where('id', $id)->first();

        if (!$address) {
            MessageService::abort(404, 'message.address.not_found');
        }

        return $address;
    }

    public function create($data)
    {



        $address = Address::create($data);

        return $address;
    }

    public function update($address, $data)
    {
        $address->update($data);

        return $address;
    }

    public function delete($address)
    {
        $address->delete();

        return $address;
    }
}
