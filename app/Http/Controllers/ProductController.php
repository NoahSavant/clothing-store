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

    public function getColor($productId, Request $request)
    {
        return response()->json($this->productService->getColor($productId, $request->all()), StatusResponse::SUCCESS);
    }

    public function getSize($productId, Request $request)
    {
        return response()->json($this->productService->getSize($productId, $request->all()), StatusResponse::SUCCESS);
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

    public function createColor($id, Request $request)
    {
        $result = $this->productService->createColor($id, $request);

        if (isset($result['errorMessage'])) {
            return response()->json([
                'errorMessage' => $result['errorMessage'],
            ], StatusResponse::ERROR);
        }

        return response()->json($result, StatusResponse::SUCCESS);
    }

    public function createSize($id, Request $request)
    {
        $result = $this->productService->createSize($id, $request);

        if (isset($result['errorMessage'])) {
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

    public function updateColor(Request $request, $id)
    {
        $result = $this->productService->updateColor($id, $request);

        if (isset($result['errorMessage'])) {
            return response()->json([
                'errorMessage' => $result['errorMessage'],
            ], StatusResponse::ERROR);
        }

        return response()->json([
            'successMessage' => 'Update color successfully'
        ], StatusResponse::SUCCESS);
    }

    public function updateSize(Request $request, $id)
    {
        $result = $this->productService->updateSize($id, $request);

        if (isset($result['errorMessage'])) {
            return response()->json([
                'errorMessage' => $result['errorMessage'],
            ], StatusResponse::ERROR);
        }

        return response()->json([
            'successMessage' => 'Update size successfully'
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

    public function deleteColor(Request $request)
    {
        $result = $this->productService->deleteColor($request->get('ids'));

        if (!$result) {
            return response()->json([
                'errorMessage' => 'Delete color fail',
            ], StatusResponse::ERROR);
        }

        return response()->json([
            'successMessage' => 'Delete color successfully',
        ], StatusResponse::SUCCESS);
    }

    public function deleteSize(Request $request)
    {
        $result = $this->productService->deleteSize($request->get('ids'));

        if (!$result) {
            return response()->json([
                'errorMessage' => 'Delete size fail',
            ], StatusResponse::ERROR);
        }

        return response()->json([
            'successMessage' => 'Delete size successfully',
        ], StatusResponse::SUCCESS);
    }
}
