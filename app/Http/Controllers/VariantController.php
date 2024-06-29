<?php

namespace App\Http\Controllers;

use App\Constants\AuthenConstants\StatusResponse;
use App\Http\Requests\DeleteFormRequest;
use App\Http\Requests\ProductFormRequests\CreateProductFormRequest;
use App\Http\Requests\VariantFormRequests\CreateVariantFormRequest;
use App\Http\Requests\VariantFormRequests\UpdateVariantFormRequest;
use App\Services\CategoryService;
use App\Services\ProductService;
use App\Services\VariantService;
use Illuminate\Http\Request;

class VariantController extends Controller
{
    protected $variantService;

    public function __construct(VariantService $variantService) {
        $this->variantService = $variantService;
    }

    public function index(Request $request, $id) {
        return response()->json($this->variantService->get($id, $request->all()), StatusResponse::SUCCESS);
    }

    public function create(CreateVariantFormRequest $request, $productId) {
        $result = $this->variantService->createVariant($productId, $request);
        if($result['errorMessage']) {
            return response()->json([
                'errorMessage' => $result['errorMessage'],
            ], StatusResponse::ERROR);
        }

        return response()->json([
            'successMessage' => "Create variant successfully",
        ], StatusResponse::SUCCESS);
    }

    // public function get(Request $request, $id) {
    //     $result = $this->productService->getSingle($id, $request);

    //     if (!$result) {
    //         return response()->json([
    //             'errorMessage' => "This product is not exits",
    //         ], StatusResponse::ERROR);
    //     }

    //     return response()->json($result, StatusResponse::SUCCESS);
    // }

    public function update(UpdateVariantFormRequest $request, $id) {
        $result = $this->variantService->update($id, $request);

        if (isset($result['errorMessage'])) {
            return response()->json([
                'errorMessage' => $result['errorMessage'],
            ], StatusResponse::ERROR);
        }

        return response()->json([
            'successMessage' => 'Update variant successfully'
        ], StatusResponse::SUCCESS);
    }

    public function delete(DeleteFormRequest $request) {
        $result = $this->variantService->delete($request->get('ids'));
        if (isset($result['errorMessage'])) {
            return response()->json($result, StatusResponse::ERROR);
        }

        return response()->json([
            'successMessage' => 'Delete variant(s) successfully',
        ], StatusResponse::ERROR);
    }
}
