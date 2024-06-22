<?php

namespace App\Http\Controllers;

use App\Constants\AuthenConstants\StatusResponse;
use App\Http\Requests\BlockFormRequests\CreateBlockRequest;
use App\Http\Requests\BlockFormRequests\CreateParentBlockRequest;
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

    public function detail($blockId)
    {
        $result = $this->blockService->detail($blockId);

        if ($result['errorMessage']) {
            return response()->json([
                'errorMessage' => $result['errorMessage'],
            ], StatusResponse::ERROR);
        }

        return response()->json([
            'data' => $result,
            'successMessage' => 'Create block successfully'
        ], StatusResponse::SUCCESS);
    }

    public function createParent(CreateParentBlockRequest $request) {
        $result = $this->blockService->createParent($request->all());

        if($result['errorMessage']) {
            return response()->json([
                'errorMessage' => $result['errorMessage'],
            ], StatusResponse::ERROR);
        }

        return response()->json([
            'block' => $result['data'],
            'successMessage' => 'Create block successfully' 
        ], StatusResponse::SUCCESS);
    }

    public function create(CreateBlockRequest $request)
    {
        $result = $this->blockService->createBlock($request->get('block_id'), $request->get('page_id'));

        if ($result['errorMessage']) {
            return response()->json([
                'errorMessage' => $result['errorMessage'],
            ], StatusResponse::ERROR);
        }

        return response()->json([
            'block' => $result['data'],
            'successMessage' => 'Create block successfully'
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
