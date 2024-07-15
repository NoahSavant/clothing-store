<?php

namespace Tests\Unit\Services\AuthenServiceTest;

use App\Constants\AuthenConstant\StatusResponse;
use App\Models\User;
use App\Services\BusinessServices\AuthenService;
use Illuminate\Http\Response;

class GetUserProfileTest extends BaseAuthenServiceTest
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testSuccess()
    {
        $user = User::create([
            'email' => 'email',
            'name' => 'name',
            'password' => '123',
            'role' => 0,
        ]);

        $authenServiceMock = $this->getMockService(AuthenService::class, ['response']);
        $this->be($user);
        $authenServiceMock->expects($this->once())
            ->method('response')
            ->willReturn(new Response(['message' => ''], StatusResponse::SUCCESS));

        $response = $authenServiceMock->getUserProfile();
        $this->assertEquals(StatusResponse::SUCCESS, $response->getStatusCode());
    }
}
