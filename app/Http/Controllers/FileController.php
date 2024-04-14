<?php

namespace App\Http\Controllers;

use App\Constants\AuthenConstants\StatusResponse;
use App\Http\Requests\DeleteFormRequest;
use App\Http\Requests\VariableFormRequests\CreateVariableRequest;
use App\Services\FileService;
use App\Services\VariableService;
use Illuminate\Http\Request;

class FileController extends Controller
{
    protected $fileService;

    public function __construct(FileService $fileService) {
        $this->fileService = $fileService;
    }

    public function upload(Request $request) {
        $result = $this->fileService->upload($request->all());

        if($result['errorMessage']) {
            return response()->json([
                'errorMessage' => $result['errorMessage'],
            ], StatusResponse::ERROR);
        }

        return response()->json([
            'variable' => $result['data'],
            'successMessage' => 'Upload file successfully' 
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
