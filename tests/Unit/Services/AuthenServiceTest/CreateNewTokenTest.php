<?php

namespace Tests\Unit\Services\AuthenServiceTest;

use App\Constants\AuthenConstant\StatusResponse;
use App\Constants\UserConstant\UserStatus;
use App\Models\User;
use App\Services\BusinessServices\AuthenService;
use Illuminate\Http\Response;

class CreateNewTokenTest extends BaseAuthenServiceTest
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
            'status' => UserStatus::ACTIVE,
        ]);

        $authenServiceMock = $this->getMockService(AuthenService::class, ['response']);
        $this->be($user);
        $authenServiceMock->expects($this->once())
            ->method('response')
            ->willReturn(new Response(['message' => ''], StatusResponse::SUCCESS));

        $response = $authenServiceMock->createNewToken('rememberToken');
        $this->assertEquals(StatusResponse::SUCCESS, $response->getStatusCode());
    }

    public function testDeactive()
    {
        $user = User::create([
            'email' => 'email',
            'password' => '123',
            'role' => 0,
            'status' => UserStatus::DEACTIVE,
        ]);

        $authenServiceMock = $this->getMockService(AuthenService::class, ['response']);
        $this->be($user);
        $authenServiceMock->expects($this->once())
            ->method('response')
            ->willReturn(new Response(['message' => ''], StatusResponse::DEACTIVED_ACCOUNT));

        $response = $authenServiceMock->createNewToken('token');
        $this->assertEquals(StatusResponse::DEACTIVED_ACCOUNT, $response->getStatusCode());
    }

    public function testBlock()
    {
        $user = User::create([
            'email' => 'email',
            'password' => '123',
            'role' => 0,
            'status' => UserStatus::BLOCK,
        ]);

        $authenServiceMock = $this->getMockService(AuthenService::class, ['response']);
        $this->be($user);
        $authenServiceMock->expects($this->once())
            ->method('response')
            ->willReturn(new Response(['message' => ''], StatusResponse::BLOCKED_ACCOUNT));

        $response = $authenServiceMock->createNewToken('token');
        $this->assertEquals(StatusResponse::BLOCKED_ACCOUNT, $response->getStatusCode());
    }
}
