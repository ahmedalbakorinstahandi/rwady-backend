<?php

namespace App\Http\Services;

use App\Http\Permissions\AddressPermission;
use App\Models\Address;
use App\Models\User;
use App\Services\FilterService;
use App\Services\LocationService;
use App\Services\MessageService;

class AddressService
{
    public function index($filters)
    {
        $query = Address::query()->with(['countryInfo', 'cityInfo']);

        $query = AddressPermission::filterIndex($query);

        $searchFields = ['name', 'address', 'extra_address', 'country', 'city', 'state', 'zipe_code'];
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
            MessageService::abort(404, 'messages.address.not_found');
        }

        $address->load(['country', 'city']);

        return $address;
    }

    public function create($data)
    {
        $address = Address::create($data);

        $address->load(['countryInfo', 'cityInfo']);

        return $address;
    }

    public function update($address, $data)
    {
        $address->update($data);

        $address->load(['countryInfo', 'cityInfo']);

        return $address;
    }

    public function delete($address)
    {
        $address->delete();

        return $address;
    }
}
