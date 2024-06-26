<?php

namespace App\Http\Controllers;

use App\Constants\AuthenConstants\StatusResponse;
use App\Http\Requests\CategoryFormRequests\CreateCategoryFormRequest;
use App\Http\Requests\DeleteFormRequest;
use App\Http\Requests\TagFormRequests\CreateTagRequest;
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

    public function create(CreateTagRequest $request) {
        $result = $this->tagService->create($request);

        if($result['errorMessage']) {
            return response()->json([
                'errorMessage' => $result['errorMessage'],
            ], StatusResponse::ERROR);
        }

        return response()->json([
            'tag' => $result['data'],
            'successMessage' => isset($result['tag_id']) ? 'Connect tag successfully' : 'Create tag successfully'
        ], StatusResponse::SUCCESS);
    }

    // public function update(UpdateAddressFormRequest $request) {
    //     $result = $this->addressService->update($request->get('ids'), $request->get('data'));

    //     if (isset($result['errorMessage'])) {
    //         return response()->json([
    //             'errorMessage' => $result['errorMessage'],
    //         ], StatusResponse::ERROR);
    //     }

    //     return response()->json([
    //         'successMessage' => 'Update address successfully'
    //     ], StatusResponse::SUCCESS);
    // }

    public function delete(DeleteFormRequest $request) {
        $result = $this->tagService->delete($request->get('ids'));

        if (!$result) {
            return response()->json([
                'errorMessage' => 'Delete tag(s) fail',
            ], StatusResponse::ERROR);
        }

        return response()->json([
            'successMessage' => 'Delete tag(s) successfully',
        ], StatusResponse::SUCCESS);
    }
}
