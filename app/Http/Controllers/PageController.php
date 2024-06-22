<?php

namespace App\Http\Controllers;

use App\Constants\AuthenConstants\StatusResponse;
use App\Http\Requests\BlockFormRequests\CreateParentBlockRequest;
use App\Http\Requests\DeleteFormRequest;
use App\Http\Requests\PageFormRequests\CreatePageRequest;
use App\Http\Requests\PageFormRequests\UpdatePageRequest;
use App\Services\BlockService;
use App\Services\PageService;
use Illuminate\Http\Request;

class PageController extends Controller
{
    protected $pageService;

    public function __construct(PageService $pageService) {
        $this->pageService = $pageService;
    }

    public function index(Request $request) {
        return response()->json($this->pageService->get($request->all()), StatusResponse::SUCCESS);
    }

    public function detail($slug)
    {
        $result = $this->pageService->detail($slug);

        if ($result['errorMessage']) {
            return response()->json([
                'errorMessage' => $result['errorMessage'],
            ], StatusResponse::ERROR);
        }

        return response()->json([
            'data' => $result,
        ], StatusResponse::SUCCESS);
    }

    public function create(CreatePageRequest $request) {
        $result = $this->pageService->create($request->all());

        if($result['errorMessage']) {
            return response()->json([
                'errorMessage' => $result['errorMessage'],
            ], StatusResponse::ERROR);
        }

        return response()->json([
            'page' => $result['data'],
            'successMessage' => 'Create page successfully' 
        ], StatusResponse::SUCCESS);
    }

    public function update(UpdatePageRequest $request) {
        $result = $this->pageService->updatePage($request->all());

        if ($result['errorMessage']) {
            return response()->json([
                'errorMessage' => $result['errorMessage'],
            ], StatusResponse::ERROR);
        }

        return response()->json([
            'page' => $result,
            'successMessage' => 'Update page successfully'
        ], StatusResponse::SUCCESS);
    }

    public function delete(DeleteFormRequest $request) {
        $result = $this->blockService->delete($request->get('ids'));

        if (!$result) {
            return response()->json([
                'errorMessage' => 'Delete block fail',
            ], StatusResponse::ERROR);
        }

        return response()->json([
            'successMessage' => 'Delete block successfully',
        ], StatusResponse::ERROR);
    }
}
