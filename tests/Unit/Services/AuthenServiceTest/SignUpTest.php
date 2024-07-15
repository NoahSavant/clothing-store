<?php

namespace Tests\Unit\Services\AuthenServiceTest;

use App\Constants\AuthenConstant\StatusResponse;
use App\Services\BusinessServices\AuthenService;
use App\Services\ModelServices\AccountVerifyService;
use App\Services\ModelServices\CompanyInformationService;
use App\Services\ModelServices\UserService;
use Illuminate\Http\Response;

class SignUpTest extends BaseAuthenServiceTest
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testSuccess()
    {
        $input = [
            'email' => 'email',
            'password' => 'password',
        ];

        $userServiceMock = $this->getMockService(UserService::class, ['isEmailExist']);
        $accountVerifyServiceMock = $this->getMockService(AccountVerifyService::class);
        $companyInformationServiceMock = $this->getMockService(CompanyInformationService::class);

        $authenServiceMock = $this->getMockService(AuthenService::class, ['hash', 'setUpUser', 'response'], [
            $userServiceMock,
            $accountVerifyServiceMock,
            $companyInformationServiceMock,
        ]);

        $authenServiceMock->expects($this->once())
            ->method('response')
            ->willReturn(new Response(['message' => 'success'], StatusResponse::SUCCESS));

        $authenServiceMock->expects($this->once())
            ->method('hash')
            ->willReturn('123456');

        $authenServiceMock->expects($this->once())
            ->method('setUpUser')
            ->willReturn('123456');

        $userServiceMock->expects($this->once())
            ->method('isEmailExist')
            ->willReturn(false);

        $response = $authenServiceMock->signup($input);
        $this->assertEquals(StatusResponse::SUCCESS, $response->getStatusCode());
    }

    public function testExistMail()
    {
        $input = [
            'email' => 'email',
            'password' => 'password',
        ];

        $userServiceMock = $this->getMockService(UserService::class, ['isEmailExist']);
        $accountVerifyServiceMock = $this->getMockService(AccountVerifyService::class);
        $companyInformationServiceMock = $this->getMockService(CompanyInformationService::class);

        $authenServiceMock = $this->getMockService(AuthenService::class, ['response'], [
            $userServiceMock,
            $accountVerifyServiceMock,
            $companyInformationServiceMock,
        ]);

        $authenServiceMock->expects($this->once())
            ->method('response')
            ->willReturn(new Response(['message' => 'fail'], StatusResponse::ERROR));

        $userServiceMock->expects($this->once())
            ->method('isEmailExist')
            ->willReturn(true);

        $response = $authenServiceMock->signup($input);
        $this->assertEquals(StatusResponse::ERROR, $response->getStatusCode());
    }
}
