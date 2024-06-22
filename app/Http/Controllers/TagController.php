<?php

namespace App\Http\Controllers;

use App\Constants\AuthenConstants\StatusResponse;
use App\Http\Requests\CategoryFormRequests\CreateCategoryFormRequest;
use App\Services\CategoryService;
use App\Services\TagService;
use Illuminate\Http\Request;

class TagController extends Controller
{
    protected $tagService;

    public function __construct(TagService $tagService) {
        $this->tagService = $tagService;
    }

    public function index(Request $request) {
        return response()->json($this->tagService->get($request->all()), StatusResponse::SUCCESS);
    }

    public function create(CreateCategoryFormRequest $request) {
        $result = $this->tagService->create($request);

        if($result['errorMessage']) {
            return response()->json([
                'errorMessage' => $result['errorMessage'],
            ], StatusResponse::ERROR);
        }

        return response()->json([
            'category' => $result['data'],
            'successMessage' => 'Create tag successfully' 
        ], StatusResponse::SUCCESS);
    }

    public function update(UpdateAddressFormRequest $request) {
        $result = $this->addressService->update($request->get('ids'), $request->get('data'));

        if (isset($result['errorMessage'])) {
            return response()->json([
                'errorMessage' => $result['errorMessage'],
            ], StatusResponse::ERROR);
        }

        return response()->json([
            'successMessage' => 'Update address successfully'
        ], StatusResponse::SUCCESS);
    }

    // public function delete(DeleteFormRequest $request) {
    //     $result = $this->addressService->delete($request->get('ids'));

    //     if (!$result) {
    //         return response()->json([
    //             'errorMessage' => 'Delete address fail',
    //         ], StatusResponse::ERROR);
    //     }

    //     return response()->json([
    //         'successMessage' => 'Delete address successfully',
    //     ], StatusResponse::ERROR);
    // }
}
