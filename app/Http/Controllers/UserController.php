<?php

namespace App\Http\Controllers;

use App\Constants\AuthenConstants\StatusResponse;
use App\Http\Requests\DeleteFormRequest;
use App\Http\Requests\UserFormRequests\ChangePasswordFormRequest;
use App\Http\Requests\UserFormRequests\CreateUserFormRequest;
use App\Http\Requests\UserFormRequests\UpdateUserFormRequest;
use App\Services\CategoryService;
use App\Services\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService) {
        $this->userService = $userService;
    }

    public function index(Request $request) {
        return response()->json($this->userService->get($request->all()), StatusResponse::SUCCESS);
    }

    public function create(CreateUserFormRequest $request) {
        $result = $this->userService->create($request);

        if(isset($result['errorMessage'])) {
            return response()->json([
                'errorMessage' => $result['errorMessage'],
            ], StatusResponse::ERROR);
        }

        return response()->json($result, StatusResponse::SUCCESS);
    }

    public function changePassword(ChangePasswordFormRequest $request)
    {
        $result = $this->userService->changePassword($request);

        if (isset($result['errorMessage'])) {
            return response()->json([
                'errorMessage' => $result['errorMessage'],
            ], StatusResponse::ERROR);
        }

        return response()->json($result, StatusResponse::SUCCESS);
    }

    public function get(Request $request, $id) {
        $result = $this->userService->getSingle($id, $request);

        if (!$result) {
            return response()->json([
                'errorMessage' => "This user is not exits",
            ], StatusResponse::ERROR);
        }

        return response()->json($result, StatusResponse::SUCCESS);
    }

    public function update(UpdateUserFormRequest $request, $id) {
        $result = $this->userService->update($id, $request);

        if (isset($result['errorMessage'])) {
            return response()->json([
                'errorMessage' => $result['errorMessage'],
            ], StatusResponse::ERROR);
        }

        return response()->json([
            'successMessage' => 'Cập nhật thông tin tài khoản thành công',
            'user' => $result['user']
        ], StatusResponse::SUCCESS);
    }

    public function delete(DeleteFormRequest $request) {
        $result = $this->userService->delete($request->get('ids'));
        if (!$result) {
            return response()->json([
                'errorMessage' => 'Delete user fail',
            ], StatusResponse::ERROR);
        }

        return response()->json([
            'successMessage' => 'Delete user successfully',
        ], StatusResponse::SUCCESS);
    }
}
