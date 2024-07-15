<?php

namespace Tests\Unit\Services\UserServiceTest;

use App\Services\ModelServices\UserService;
use Tests\Unit\BaseTest;

class GetAllUserTest extends BaseTest
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testSuccess()
    {
        $usereServiceMock = $this->getMockService(UserService::class);

        $response = $usereServiceMock->getAllUser();
        $this->assertInstanceOf("Illuminate\Database\Eloquent\Collection", $response);
    }
}
