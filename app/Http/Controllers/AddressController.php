<?php

namespace App\Http\Controllers;

use App\Http\Permissions\AddressPermission;
use App\Http\Requests\Address\CreateAddressRequest;
use App\Http\Requests\Address\UpdateAddressRequest;
use App\Http\Resources\AddressResource;
use App\Http\Services\AddressService;
use App\Services\ResponseService;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    protected $addressService;

    public function __construct(AddressService $addressService)
    {
        $this->addressService = $addressService;
    }

    public function index(Request $request)
    {
        $addresses = $this->addressService->index($request->all());

        return ResponseService::response(
            [
                'success' => true,
                'data' => $addresses,
                'resource' => AddressResource::class,
                'meta' => true,
                'status' => 200,
            ]
        );
    }

    public function show(int $id)
    {
        $address = $this->addressService->show($id);

        $address = AddressPermission::canShow($address);

        return ResponseService::response(
            [
                'success' => true,
                'data' => $address,
                'resource' => AddressResource::class,
                'status' => 200,
            ]
        );
    }

    public function create(CreateAddressRequest $request)
    {

        $data = $request->validated();

        $data = AddressPermission::create($data);

        $address = $this->addressService->create($data);


        return ResponseService::response(
            [
                'success' => true,
                'data' => $address,
                'message' => 'messages.address.created_successfully',
                'resource' => AddressResource::class,
                'status' => 201,
            ]
        );
    }

    public function update(UpdateAddressRequest $request, int $id)
    {
        $address = $this->addressService->show($id);

        $data = $request->validated();
        $data = AddressPermission::canUpdate($address, $data);

        $address = $this->addressService->update($address, $data);


        return ResponseService::response(
            [
                'success' => true,
                'data' => $address,
                'message' => 'messages.address.updated_successfully',
                'resource' => AddressResource::class,
                'status' => 200,
            ]
        );
    }

    public function delete(int $id)
    {
        $address = $this->addressService->show($id);

        $address = $this->addressService->delete($address);

        return ResponseService::response(
            [
                'success' => true,
                'message' => 'messages.address.deleted_successfully',
                'status' => 200,
            ]
        );
    }
}
