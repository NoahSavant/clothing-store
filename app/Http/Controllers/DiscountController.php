<?php

namespace App\Http\Controllers;

use App\Constants\AuthenConstants\StatusResponse;
use App\Http\Requests\DiscountFormRequests\CreateDiscountRequest;
use App\Http\Requests\DiscountFormRequests\UpdateDiscountRequest;
use App\Http\Requests\DeleteFormRequest;
use App\Services\DiscountService;
use Illuminate\Http\Request;

class DiscountController extends Controller
{
    protected $discountService;

    public function __construct(DiscountService $discountService) {
        $this->discountService = $discountService;
    }

    public function index(Request $request) {
        return response()->json($this->discountService->get($request->all()), StatusResponse::SUCCESS);
    }

    public function create(CreateDiscountRequest $request) {
        $result = $this->discountService->create($request);

        if($result['errorMessage']) {
            return response()->json([
                'errorMessage' => $result['errorMessage'],
            ], StatusResponse::ERROR);
        }

        return response()->json([
            'discount' => $result['data'],
            'successMessage' => 'Create discount successfully' 
        ], StatusResponse::SUCCESS);
    }

    public function update(UpdateDiscountRequest $request, $id)
    {
        $result = $this->discountService->update($id, $request);

        if (isset($result['errorMessage'])) {
            return response()->json([
                'errorMessage' => $result['errorMessage'],
            ], StatusResponse::ERROR);
        }

        return response()->json([
            'successMessage' => 'Update discount successfully'
        ], StatusResponse::SUCCESS);
    }

    public function delete(DeleteFormRequest $request)
    {
        $result = $this->discountService->delete($request->get('ids'));

        if (!$result) {
            return response()->json([
                'errorMessage' => 'Delete discount fail',
            ], StatusResponse::ERROR);
        }

        return response()->json([
            'successMessage' => 'Delete discount successfully',
        ], StatusResponse::SUCCESS);
    }
}
