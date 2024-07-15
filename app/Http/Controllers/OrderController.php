<?php

namespace App\Http\Controllers;

use App\Constants\AuthenConstants\StatusResponse;
use App\Http\Requests\DeleteFormRequest;
use App\Http\Requests\OrderFormRequests\CreateOrderFormRequest;
use App\Http\Requests\OrderFormRequests\UpdateOrderFormRequest;
use App\Services\CategoryService;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService) {
        $this->orderService = $orderService;
    }

    public function index(Request $request) {
        return response()->json($this->orderService->get($request->all()), StatusResponse::SUCCESS);
    }

    public function create(CreateOrderFormRequest $request) {
        $result = $this->orderService->create($request);

        if(isset($result['errorMessage'])) {
            return response()->json([
                'errorMessage' => $result['errorMessage'],
            ], StatusResponse::ERROR);
        }

        return response()->json($result, StatusResponse::SUCCESS);
    }

    public function get(Request $request, $id) {
        $result = $this->orderService->getSingle($id, $request);

        return response()->json($result, StatusResponse::SUCCESS);
    }

    public function update(UpdateOrderFormRequest $request, $id) {
        $result = $this->orderService->update($id, $request);

        if (isset($result['errorMessage'])) {
            return response()->json([
                'errorMessage' => $result['errorMessage'],
            ], StatusResponse::ERROR);
        }

        return response()->json([
            'successMessage' => 'Cập nhật trạng thái đơn hàng thành công'
        ], StatusResponse::SUCCESS);
    }

    public function delete(DeleteFormRequest $request) {
        $result = $this->orderService->delete($request->get('ids'));

        if (!$result) {
            return response()->json([
                'errorMessage' => 'Delete order fail',
            ], StatusResponse::ERROR);
        }

        return response()->json([
            'successMessage' => 'Delete order successfully',
        ], StatusResponse::SUCCESS);
    }
}
