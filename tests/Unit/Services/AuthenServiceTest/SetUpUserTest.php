<?php

namespace Tests\Unit\Services\AuthenServiceTest;

use App\Constants\UserConstant\UserRole;
use App\Constants\UserConstant\UserStatus;
use App\Services\BusinessServices\AuthenService;
use App\Services\ModelServices\AccountVerifyService;
use App\Services\ModelServices\CompanyInformationService;
use App\Services\ModelServices\UserService;

class SetUpUserTest extends BaseAuthenServiceTest
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testSuccess()
    {
        $data = [
            'email' => 'email',
            'password' => '123',
            'role' => 0,
            'status' => UserStatus::DEACTIVE,
        ];

        $userServiceMock = $this->getMockService(UserService::class);
        $accountVerifyServiceMock = $this->getMockService(AccountVerifyService::class, ['create']);
        $companyInformationServiceMock = $this->getMockService(CompanyInformationService::class);

        $authenServiceMock = $this->getMockService(AuthenService::class, ['createVerify', 'sendMailQueue'], [
            $userServiceMock,
            $accountVerifyServiceMock,
            $companyInformationServiceMock,
        ]);

        $authenServiceMock->expects($this->once())
            ->method('sendMailQueue')
            ->willReturn('true');

        $authenServiceMock->expects($this->once())
            ->method('createVerify')
            ->willReturn('true');

        $accountVerifyServiceMock->expects($this->once())
            ->method('create')
            ->willReturn('true');

        $response = $authenServiceMock->setUpUser($data);
        $this->assertIsObject($response);
    }

    public function testSuccessWithRecruiter()
    {
        $data = [
            'email' => 'email',
            'password' => '123',
            'role' => UserRole::RECRUITER,
            'status' => UserStatus::DEACTIVE,
        ];

        $userServiceMock = $this->getMockService(UserService::class);
        $accountVerifyServiceMock = $this->getMockService(AccountVerifyService::class, ['create']);
        $companyInformationServiceMock = $this->getMockService(CompanyInformationService::class, ['create']);

        $authenServiceMock = $this->getMockService(AuthenService::class, ['createVerify', 'sendMailQueue'], [
            $userServiceMock,
            $accountVerifyServiceMock,
            $companyInformationServiceMock,
        ]);

        $authenServiceMock->expects($this->once())
            ->method('sendMailQueue')
            ->willReturn('true');

        $authenServiceMock->expects($this->once())
            ->method('createVerify')
            ->willReturn('true');

        $accountVerifyServiceMock->expects($this->once())
            ->method('create')
            ->willReturn('true');

        $companyInformationServiceMock->expects($this->once())
            ->method('create')
            ->willReturn('true');

        $response = $authenServiceMock->setUpUser($data);
        $this->assertIsObject($response);
    }
}
