<?php

namespace App\Http\Controllers;

use App\Constants\AuthenConstants\StatusResponse;
use App\Http\Requests\CollectionFormRequests\CreateCollectionFormRequest;
use App\Http\Requests\CollectionFormRequests\UpdateCollectionFormRequest;
use App\Http\Requests\DeleteFormRequest;
use App\Services\CollectionService;
use Illuminate\Http\Request;

class CollectionController extends Controller
{
    protected $collectionService;

    public function __construct(CollectionService $collectionService)
    {
        $this->collectionService = $collectionService;
    }

    public function index(Request $request)
    {
        return response()->json($this->collectionService->get($request->all()), StatusResponse::SUCCESS);
    }

    public function create(CreateCollectionFormRequest $request)
    {
        $result = $this->collectionService->create($request);

        if ($result['errorMessage']) {
            return response()->json([
                'errorMessage' => $result['errorMessage'],
            ], StatusResponse::ERROR);
        }

        return response()->json([
            'collection' => $result['data'],
            'successMessage' => 'Create collection successfully'
        ], StatusResponse::SUCCESS);
    }

    public function get(Request $request, $id)
    {
        $result = $this->collectionService->getSingle($id, $request);

        if (!$result) {
            return response()->json([
                'errorMessage' => "This collection is not exits",
            ], StatusResponse::ERROR);
        }

        return response()->json($result, StatusResponse::SUCCESS);
    }

    public function updateProducts(Request $request, $id) {
        $result = $this->collectionService->updateProducts($id, $request);

        if (isset($result['errorMessage'])) {
            return response()->json([
                'errorMessage' => $result['errorMessage'],
            ], StatusResponse::ERROR);
        }

        return response()->json([
            'successMessage' => 'Update products successfully'
        ], StatusResponse::SUCCESS);
    }

    public function update(UpdateCollectionFormRequest $request, $id)
    {
        $result = $this->collectionService->update($id, $request);

        if (isset($result['errorMessage'])) {
            return response()->json([
                'errorMessage' => $result['errorMessage'],
            ], StatusResponse::ERROR);
        }

        return response()->json([
            'successMessage' => 'Update collection successfully'
        ], StatusResponse::SUCCESS);
    }

    public function delete(DeleteFormRequest $request)
    {
        $result = $this->collectionService->delete($request->get('ids'));

        if (!$result) {
            return response()->json([
                'errorMessage' => 'Delete collection fail',
            ], StatusResponse::ERROR);
        }

        return response()->json([
            'successMessage' => 'Delete collection successfully',
        ], StatusResponse::SUCCESS);
    }
}
