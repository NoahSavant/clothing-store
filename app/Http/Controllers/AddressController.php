<?php

namespace App\Http\Controllers;

use App\Constants\AuthenConstants\StatusResponse;
use App\Http\Requests\AddressFormRequest\CreateAddressFormRequest;
use App\Http\Requests\AddressFormRequest\DeleteAddressFormRequest;
use App\Http\Requests\AddressFormRequest\UpdateAddressFormRequest;
use App\Services\AddressService;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    protected $addressService;

    public function __construct(AddressService $addressService) {
        $this->addressService = $addressService;
    }

    public function index(Request $request) {
        return response()->json($this->addressService->get($request->all()), StatusResponse::SUCCESS);
    }

    public function create(CreateAddressFormRequest $request) {
        $result = $this->addressService->create($request->all());

        if($result['errorMessage']) {
            return response()->json([
                'errorMessage' => $result['errorMessage'],
            ], StatusResponse::ERROR);
        }

        return response()->json([
            'address' => $result['data'],
            'successMessage' => 'Create address successfully' 
        ], StatusResponse::SUCCESS);
    }

    public function update(UpdateAddressFormRequest $request) {
        $result = $this->addressService->update($request->all());

        if ($result['errorMessage']) {
            return response()->json([
                'errorMessage' => $result['errorMessage'],
            ], StatusResponse::ERROR);
        }

        return response()->json([
            'successMessage' => 'Update address successfully'
        ], StatusResponse::SUCCESS);
    }

    public function delete(DeleteAddressFormRequest $request) {
        $result = $this->addressService->update($request->get('ids'), $request->get('data'));

        if (!$result) {
            return response()->json([
                'errorMessage' => 'Update address fail',
            ], StatusResponse::ERROR);
        }

        return response()->json([
            'successMessage' => 'Update address successfully',
        ], StatusResponse::ERROR);
    }
}
