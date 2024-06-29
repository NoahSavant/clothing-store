<?php

namespace App\Http\Controllers;

use App\Constants\AuthenConstants\StatusResponse;
use App\Http\Requests\DeleteFormRequest;
use App\Http\Requests\ProductFormRequests\CreateProductFormRequest;
use App\Http\Requests\ProductFormRequests\UpdateProductFormRequest;
use App\Services\CategoryService;
use App\Services\ProductService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    protected $productService;

    public function __construct(ProductService $productService) {
        $this->productService = $productService;
    }

    public function index(Request $request) {
        return response()->json($this->productService->get($request->all()), StatusResponse::SUCCESS);
    }

    public function create(CreateProductFormRequest $request) {
        $result = $this->productService->create($request);

        if(isset($result['errorMessage'])) {
            return response()->json([
                'errorMessage' => $result['errorMessage'],
            ], StatusResponse::ERROR);
        }

        return response()->json($result, StatusResponse::SUCCESS);
    }

    public function get(Request $request, $id) {
        $result = $this->productService->getSingle($id, $request);

        if (!$result) {
            return response()->json([
                'errorMessage' => "This product is not exits",
            ], StatusResponse::ERROR);
        }

        return response()->json($result, StatusResponse::SUCCESS);
    }

    public function update(UpdateProductFormRequest $request, $id) {
        $result = $this->productService->update($id, $request);

        if (isset($result['errorMessage'])) {
            return response()->json([
                'errorMessage' => $result['errorMessage'],
            ], StatusResponse::ERROR);
        }

        return response()->json([
            'successMessage' => 'Update product successfully'
        ], StatusResponse::SUCCESS);
    }

    public function delete(DeleteFormRequest $request) {
        $result = $this->productService->delete($request->get('ids'));

        if (!$result) {
            return response()->json([
                'errorMessage' => 'Delete product fail',
            ], StatusResponse::ERROR);
        }

        return response()->json([
            'successMessage' => 'Delete product successfully',
        ], StatusResponse::SUCCESS);
    }
}
