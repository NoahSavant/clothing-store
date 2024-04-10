<?php

namespace App\Http\Controllers;

use App\Constants\AuthenConstants\StatusResponse;
use App\Http\Requests\DeleteFormRequest;
use App\Http\Requests\VariableFormRequests\CreateVariableRequest;
use App\Services\VariableService;
use Request;

class VariableController extends Controller
{
    protected $variableService;

    public function __construct(VariableService $variableService) {
        $this->variableService = $variableService;
    }

    public function create(CreateVariableRequest $request) {
        $result = $this->variableService->create($request->all());

        if($result['errorMessage']) {
            return response()->json([
                'errorMessage' => $result['errorMessage'],
            ], StatusResponse::ERROR);
        }

        return response()->json([
            'variable' => $result['data'],
            'successMessage' => 'Create variable successfully' 
        ], StatusResponse::SUCCESS);
    }

    // public function update(UpdateAddressFormRequest $request) {
    //     $result = $this->addressService->update($request->all());

    //     if ($result['errorMessage']) {
    //         return response()->json([
    //             'errorMessage' => $result['errorMessage'],
    //         ], StatusResponse::ERROR);
    //     }

    //     return response()->json([
    //         'successMessage' => 'Update address successfully'
    //     ], StatusResponse::SUCCESS);
    // }

    public function delete(DeleteFormRequest $request) {
        $result = $this->blockService->delete($request->get('ids'));

        if (!$result) {
            return response()->json([
                'errorMessage' => 'Delete block fail',
            ], StatusResponse::ERROR);
        }

        return response()->json([
            'successMessage' => 'Delete block successfully',
        ], StatusResponse::ERROR);
    }
}
