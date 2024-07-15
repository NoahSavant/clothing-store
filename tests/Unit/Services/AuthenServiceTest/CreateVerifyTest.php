<?php

namespace Tests\Unit\Services\AuthenServiceTest;

use App\Constants\UserConstant\UserStatus;
use App\Models\AccountVerify;
use App\Models\User;
use App\Services\BusinessServices\AuthenService;
use App\Services\ModelServices\AccountVerifyService;
use App\Services\ModelServices\CompanyInformationService;
use App\Services\ModelServices\UserService;

class CreateVerifyTest extends BaseAuthenServiceTest
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

        $authenServiceMock = $this->getMockService(AuthenService::class, ['hash'], [
            $userServiceMock,
            $accountVerifyServiceMock,
            $companyInformationServiceMock,
        ]);

        $authenServiceMock->expects($this->once())
            ->method('hash')
            ->willReturn('123456');

        $response = $authenServiceMock->createVerify($user->email);
        $this->assertIsString($response);
    }

    public function testFail()
    {
        $userServiceMock = $this->getMockService(UserService::class);
        $accountVerifyServiceMock = $this->getMockService(AccountVerifyService::class);
        $companyInformationServiceMock = $this->getMockService(CompanyInformationService::class);

        $authenServiceMock = $this->getMockService(AuthenService::class, [], [
            $userServiceMock,
            $accountVerifyServiceMock,
            $companyInformationServiceMock,
        ]);

        $response = $authenServiceMock->createVerify('email');
        $this->assertNull($response);
    }
}
