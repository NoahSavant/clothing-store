<?php

namespace Tests\Unit\Services\AuthenServiceTest;

use App\Constants\AuthenConstant\StatusResponse;
use App\Constants\UserConstant\UserStatus;
use App\Models\AccountVerify;
use App\Models\User;
use App\Services\BusinessServices\AuthenService;
use App\Services\ModelServices\AccountVerifyService;
use App\Services\ModelServices\CompanyInformationService;
use App\Services\ModelServices\UserService;
use Illuminate\Http\Response;

class ZActiveAccountTest extends BaseAuthenServiceTest
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testSuccess()
    {
        $user = User::create([
            'email' => 'email',
            'password' => '123',
            'role' => 0,
            'status' => UserStatus::DEACTIVE,
        ]);

        AccountVerify::create([
            'user_id' => $user->id,
        ]);

        $userServiceMock = $this->getMockService(UserService::class);
        $accountVerifyServiceMock = $this->getMockService(AccountVerifyService::class);
        $companyInformationServiceMock = $this->getMockService(CompanyInformationService::class);

        $authenServiceMock = $this->getMockService(AuthenService::class, ['checkVerifyAccount', 'response'], [
            $userServiceMock,
            $accountVerifyServiceMock,
            $companyInformationServiceMock,
        ]);

        $authenServiceMock->expects($this->once())
            ->method('checkVerifyAccount')
            ->willReturn(false);

        $authenServiceMock->expects($this->once())
            ->method('response')
            ->willReturn(new Response(['message' => ''], StatusResponse::SUCCESS));

        $response = $authenServiceMock->activeAccount([
            'email' => 'email',
            'verify_code' => 'code',
        ]);
        $this->assertEquals(StatusResponse::SUCCESS, $response->getStatusCode());
    }

    public function testInvalidVerifyAccount()
    {
        $user = User::create([
            'email' => 'email',
            'password' => '123',
            'role' => 0,
            'status' => UserStatus::DEACTIVE,
        ]);

        AccountVerify::create([
            'user_id' => $user->id,
        ]);

        $userServiceMock = $this->getMockService(UserService::class);
        $accountVerifyServiceMock = $this->getMockService(AccountVerifyService::class);
        $companyInformationServiceMock = $this->getMockService(CompanyInformationService::class);

        $authenServiceMock = $this->getMockService(AuthenService::class, ['checkVerifyAccount'], [
            $userServiceMock,
            $accountVerifyServiceMock,
            $companyInformationServiceMock,
        ]);

        $authenServiceMock->expects($this->once())
            ->method('checkVerifyAccount')
            ->willReturn(true);

        $response = $authenServiceMock->activeAccount([
            'email' => 'email',
            'verify_code' => 'code',
        ]);
        $this->assertEquals(true, $response);
    }
}
