<?php

namespace App\Http\Controllers;

use App\Constants\AuthenConstants\StatusResponse;
use App\Http\Requests\DeleteFormRequest;
use App\Http\Requests\BlogFormRequests\CreateBlogFormRequest;
use App\Http\Requests\BlogFormRequests\UpdateBlogFormRequest;
use App\Services\CategoryService;
use App\Services\BlogService;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    protected $blogService;

    public function __construct(BlogService $blogService) {
        $this->blogService = $blogService;
    }

    public function index(Request $request) {
        return response()->json($this->blogService->get($request->all()), StatusResponse::SUCCESS);
    }

    public function create(CreateBlogFormRequest $request) {
        $result = $this->blogService->create($request);

        if(isset($result['errorMessage'])) {
            return response()->json([
                'errorMessage' => $result['errorMessage'],
            ], StatusResponse::ERROR);
        }

        return response()->json($result, StatusResponse::SUCCESS);
    }

    public function get(Request $request, $id) {
        $result = $this->blogService->getSingle($id, $request);

        if (!$result) {
            return response()->json([
                'errorMessage' => "This blog is not exits",
            ], StatusResponse::ERROR);
        }

        return response()->json($result, StatusResponse::SUCCESS);
    }

    public function update(UpdateBlogFormRequest $request, $id) {
        $result = $this->blogService->update($id, $request);

        if (isset($result['errorMessage'])) {
            return response()->json([
                'errorMessage' => $result['errorMessage'],
            ], StatusResponse::ERROR);
        }

        return response()->json([
            'successMessage' => 'Update blog successfully'
        ], StatusResponse::SUCCESS);
    }

    public function delete(DeleteFormRequest $request) {
        $result = $this->blogService->delete($request->get('ids'));

        if (!$result) {
            return response()->json([
                'errorMessage' => 'Delete blog fail',
            ], StatusResponse::ERROR);
        }

        return response()->json([
            'successMessage' => 'Delete blog successfully',
        ], StatusResponse::SUCCESS);
    }
}
