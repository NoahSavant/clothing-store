<?php

namespace App\Http\Controllers;

use App\Constants\AuthenConstants\StatusResponse;
use App\Http\Requests\CategoryFormRequests\CreateCategoryFormRequest;
use App\Http\Requests\CategoryFormRequests\UpdateCategoryFormRequest;
use App\Http\Requests\DeleteFormRequest;
use App\Services\CategoryService;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    protected $categoryService;

    public function __construct(CategoryService $categoryService) {
        $this->categoryService = $categoryService;
    }

    public function index(Request $request) {
        return response()->json($this->categoryService->get($request->all()), StatusResponse::SUCCESS);
    }

    public function create(CreateCategoryFormRequest $request) {
        $result = $this->categoryService->create($request);

        if($result['errorMessage']) {
            return response()->json([
                'errorMessage' => $result['errorMessage'],
            ], StatusResponse::ERROR);
        }

        return response()->json([
            'category' => $result['data'],
            'successMessage' => 'Create category successfully' 
        ], StatusResponse::SUCCESS);
    }

    public function update(UpdateCategoryFormRequest $request, $id)
    {
        $result = $this->categoryService->update($id, $request);

        if (isset($result['errorMessage'])) {
            return response()->json([
                'errorMessage' => $result['errorMessage'],
            ], StatusResponse::ERROR);
        }

        return response()->json([
            'successMessage' => 'Update category successfully'
        ], StatusResponse::SUCCESS);
    }

    public function delete(DeleteFormRequest $request)
    {
        $result = $this->categoryService->delete($request->get('ids'));

        if (!$result) {
            return response()->json([
                'errorMessage' => 'Delete category fail',
            ], StatusResponse::ERROR);
        }

        return response()->json([
            'successMessage' => 'Delete category successfully',
        ], StatusResponse::SUCCESS);
    }
}
