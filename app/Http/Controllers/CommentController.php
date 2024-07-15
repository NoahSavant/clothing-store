<?php

namespace App\Http\Controllers;

use App\Constants\AuthenConstants\StatusResponse;
use App\Http\Requests\DeleteFormRequest;
use App\Http\Requests\CommentFormRequests\CreateCommentFormRequest;
use App\Http\Requests\CommentFormRequests\UpdateCommentFormRequest;
use App\Services\CategoryService;
use App\Services\CommentService;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    protected $commentService;

    public function __construct(CommentService $commentService) {
        $this->commentService = $commentService;
    }

    public function index(Request $request) {
        return response()->json($this->commentService->get($request->all()), StatusResponse::SUCCESS);
    }

    public function create(CreateCommentFormRequest $request) {
        $result = $this->commentService->create($request);

        if(isset($result['errorMessage'])) {
            return response()->json([
                'errorMessage' => $result['errorMessage'],
            ], StatusResponse::ERROR);
        }

        return response()->json($result, StatusResponse::SUCCESS);
    }

    public function get(Request $request, $id) {
        $result = $this->commentService->getSingle($id, $request);

        if (!$result) {
            return response()->json([
                'errorMessage' => "This comment is not exits",
            ], StatusResponse::ERROR);
        }

        return response()->json($result, StatusResponse::SUCCESS);
    }

    public function update(UpdateCommentFormRequest $request, $id) {
        $result = $this->commentService->update($id, $request);

        if (isset($result['errorMessage'])) {
            return response()->json([
                'errorMessage' => $result['errorMessage'],
            ], StatusResponse::ERROR);
        }

        return response()->json([
            'successMessage' => 'Update comment successfully'
        ], StatusResponse::SUCCESS);
    }

    public function delete(DeleteFormRequest $request) {
        $result = $this->commentService->delete($request->get('ids'));

        if (isset($result['errorMessage'])) {
            return response()->json($result, StatusResponse::ERROR);
        }

        return response()->json($result, StatusResponse::SUCCESS);
    }
}
