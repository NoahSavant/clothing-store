<?php

namespace App\Http\Controllers;

use App\Constants\AuthenConstants\StatusResponse;
use App\Http\Requests\DeleteFormRequest;
use App\Services\BlockService;
use Illuminate\Http\Request;

class BlockController extends Controller
{
    protected $blockService;

    public function __construct(BlockService $blockService) {
        $this->blockService = $blockService;
    }

    public function index(Request $request) {
        return response()->json($this->blockService->get($request->all()), StatusResponse::SUCCESS);
    }

    // public function create(CreateAddressFormRequest $request) {
    //     $result = $this->addressService->create($request->all());

    //     if($result['errorMessage']) {
    //         return response()->json([
    //             'errorMessage' => $result['errorMessage'],
    //         ], StatusResponse::ERROR);
    //     }

    //     return response()->json([
    //         'address' => $result['data'],
    //         'successMessage' => 'Create address successfully' 
    //     ], StatusResponse::SUCCESS);
    // }

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
