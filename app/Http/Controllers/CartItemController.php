<?php

namespace App\Http\Controllers;

use App\Constants\AuthenConstants\StatusResponse;
use App\Http\Requests\DeleteFormRequest;
use App\Http\Requests\CartItemFormRequests\CreateCartItemFormRequest;
use App\Http\Requests\CartItemFormRequests\UpdateCartItemFormRequest;
use App\Services\CategoryService;
use App\Services\CartItemService;
use Illuminate\Http\Request;

class CartItemController extends Controller
{
    protected $cartItemService;

    public function __construct(CartItemService $cartItemService) {
        $this->cartItemService = $cartItemService;
    }

    public function index(Request $request) {
        return response()->json($this->cartItemService->get($request->all()), StatusResponse::SUCCESS);
    }

    public function create(Request $request) {
        $result = $this->cartItemService->create($request);

        if(isset($result['errorMessage'])) {
            return response()->json([
                'errorMessage' => $result['errorMessage'],
            ], StatusResponse::ERROR);
        }

        return response()->json($result, StatusResponse::SUCCESS);
    }

    public function update(Request $request, $id) {
        $result = $this->cartItemService->update($id, $request);

        if (isset($result['errorMessage'])) {
            return response()->json([
                'errorMessage' => $result['errorMessage'],
            ], StatusResponse::ERROR);
        }

        return response()->json([
            'successMessage' => 'Cập nhật sản phẩm thành công'
        ], StatusResponse::SUCCESS);
    }

    public function delete(DeleteFormRequest $request) {
        $result = $this->cartItemService->delete($request->get('ids'));

        if (!$result) {
            return response()->json([
                'errorMessage' => 'Xóa sản phẩm khỏi giỏ hàng thất bại',
            ], StatusResponse::ERROR);
        }

        return response()->json([
            'successMessage' => 'Xóa sản phẩm khỏi giỏ hàng thành công',
        ], StatusResponse::SUCCESS);
    }
}
