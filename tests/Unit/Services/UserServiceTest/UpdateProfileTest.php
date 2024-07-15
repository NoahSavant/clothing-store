<?php

namespace Tests\Unit\Services\UserServiceTest;

use App\Constants\UserConstant\UserRole;
use App\Constants\UserConstant\UserStatus;
use App\Models\User;
use App\Services\ModelServices\UserService;
use Tests\Unit\BaseTest;

class UpdateProfileTest extends BaseTest
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
            'role' => UserRole::ADMIN,
            'status' => UserStatus::ACTIVE,
        ]);

        $input = [
            'first_name' => 'name',
        ];

        $userServiceMock = $this->getMockService(UserService::class);

        $response = $userServiceMock->updateProfile($user, $input);
        $this->assertIsObject($response);
    }

    public function testUserNotFound()
    {
        $userServiceMock = $this->getMockService(UserService::class);

        $response = $userServiceMock->updateProfile(null, []);
        $this->assertFalse($response);
    }
}
