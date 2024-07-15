<?php

namespace Tests\Unit\Services\AuthenServiceTest;

use App\Constants\AuthenConstant\StatusResponse;
use App\Constants\UserConstant\UserStatus;
use App\Models\User;
use App\Services\BusinessServices\AuthenService;
use Illuminate\Http\Response;

class SendVerifyTest extends BaseAuthenServiceTest
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

        $input = [
            'email' => $user->email,
        ];

        $authenServiceMock = $this->getMockService(AuthenService::class, ['createVerify', 'response', 'sendMailQueue']);

        $authenServiceMock->expects($this->once())
            ->method('response')
            ->willReturn(new Response(['message' => 'success'], StatusResponse::SUCCESS));

        $authenServiceMock->expects($this->once())
            ->method('sendMailQueue')
            ->willReturn(true);

        $authenServiceMock->expects($this->once())
            ->method('createVerify')
            ->willReturn(true);

        $response = $authenServiceMock->sendVerify($input);
        $this->assertEquals(StatusResponse::SUCCESS, $response->getStatusCode());
    }

    public function testFail()
    {
        $input = [
            'email' => 'name',
        ];

        $authenServiceMock = $this->getMockService(AuthenService::class, ['createVerify', 'response']);

        $authenServiceMock->expects($this->once())
            ->method('response')
            ->willReturn(new Response(['message' => 'fail'], StatusResponse::ERROR));

        $authenServiceMock->expects($this->once())
            ->method('createVerify')
            ->willReturn(false);

        $response = $authenServiceMock->sendVerify($input);
        $this->assertEquals(StatusResponse::ERROR, $response->getStatusCode());
    }
}
