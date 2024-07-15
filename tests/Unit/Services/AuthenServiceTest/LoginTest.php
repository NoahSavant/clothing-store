<?php

namespace Tests\Unit\Services\AuthenServiceTest;

use App\Constants\AuthenConstant\StatusResponse;
use App\Constants\UserConstant\UserStatus;
use App\Models\User;
use App\Services\BusinessServices\AuthenService;
use Illuminate\Http\Response;

class LoginTest extends BaseAuthenServiceTest
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
            'password' => '123',
        ];

        $authenServiceMock = $this->getMockService(AuthenService::class, ['createNewToken']);

        $authenServiceMock->expects($this->once())
            ->method('createNewToken')
            ->willReturn(new Response(['message' => 'newToken'], StatusResponse::SUCCESS));

        $response = $authenServiceMock->login($input);
        $this->assertEquals(StatusResponse::SUCCESS, $response->getStatusCode());
    }

    public function testLoginFail()
    {
        $input = [
            'email' => 'test@example.com',
            'password' => 'password',
        ];
        $authenServiceMock = $this->getMockService(AuthenService::class, ['response']);

        $authenServiceMock->expects($this->once())
            ->method('response')
            ->willReturn(new Response(['message' => 'fail'], StatusResponse::UNAUTHORIZED));

        $response = $authenServiceMock->login($input);
        $this->assertEquals(StatusResponse::UNAUTHORIZED, $response->getStatusCode());
    }
}
