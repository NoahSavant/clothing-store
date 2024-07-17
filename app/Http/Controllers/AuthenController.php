<?php

namespace App\Http\Controllers;

use App\Constants\AuthenConstants\StatusResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\AuthenFormRequests\LoginFormRequest;
use App\Http\Requests\AuthenFormRequests\RefreshFormRequest;
use App\Http\Requests\AuthenFormRequests\ResetPasswordFormRequest;
use App\Http\Requests\AuthenFormRequests\SendVerifyFormRequest;
use App\Http\Requests\AuthenFormRequests\SignUpFormRequest;
use App\Http\Requests\AuthenFormRequests\VerifyAccountFormRequest;
use App\Services\AuthenService;
use Illuminate\Http\Request;

class AuthenController extends Controller
{
    protected $authenService;

    protected $connectionService;

    public function __construct(AuthenService $authenService)
    {
        $this->authenService = $authenService;
    }

    public function test(Request $request)
    {
        return $this->authenService->test();
    }

    public function login(LoginFormRequest $request)
    {
        return $this->authenService->login($request->all());
    }

    public function signUp(SignUpFormRequest $request)
    {
        return $this->authenService->signUp($request->all());
    }

    public function activeAccount(VerifyAccountFormRequest $request) {
        return $this->authenService->activeAccount($request->all());
    }

    public function resetPassword(ResetPasswordFormRequest $request)
    {
        return $this->authenService->resetPassword($request->all());
    }

    public function throwAuthenError()
    {
        return $this->authenService->throwAuthenError();
    }

    public function throwAuthorError()
    {
        return $this->authenService->throwAuthorError();
    }

    public function refresh(RefreshFormRequest $request)
    {
        $result = $this->authenService->refresh($request->get('remember_token'));
        if (! $result) {
            return response()->json([
                'error' => 'Can not find out this remember token',
            ], StatusResponse::ERROR);
        }

        return response()->json($result, StatusResponse::SUCCESS);
    }

    public function getUserProfile()
    {
        return $this->authenService->getUserProfile();
    }

    protected function createNewToken($token)
    {
        return $this->authenService->createNewToken($token, null);
    }

    public function sendVerify(SendVerifyFormRequest $request)
    {
        return $this->authenService->sendVerify($request->all());
    }
}
