<?php

namespace Tests\Unit\Services\UserServiceTest;

use App\Constants\UserConstant\UserRole;
use App\Constants\UserConstant\UserStatus;
use App\Models\CompanyInformation;
use App\Models\User;
use App\Services\ModelServices\AccountVerifyService;
use App\Services\ModelServices\ApplicationService;
use App\Services\ModelServices\CompanyInformationService;
use App\Services\ModelServices\RecruitmentPostService;
use App\Services\ModelServices\ResumeService;
use App\Services\ModelServices\UserService;
use Tests\Unit\BaseTest;

class DeleteTest extends BaseTest
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
            'role' => UserRole::EMPLOYEE,
            'status' => UserStatus::ACTIVE,
        ]);

        $user2 = User::create([
            'email' => 'email2',
            'password' => '123',
            'role' => UserRole::RECRUITER,
            'status' => UserStatus::ACTIVE,
        ]);

        CompanyInformation::create([
            'user_id' => $user2->id,
        ]);

        $accountVerifyServiceMock = $this->getMockService(AccountVerifyService::class);
        $companyInformationServiceMock = $this->getMockService(CompanyInformationService::class, ['delete']);
        $recruitmentPostServiceMock = $this->getMockService(RecruitmentPostService::class, ['delete']);
        $resumeServiceMock = $this->getMockService(ResumeService::class, ['delete']);
        $applicationServiceMock = $this->getMockService(ApplicationService::class, ['delete']);

        $usereServiceMock = $this->getMockService(UserService::class, [], [
            new User(),
            $accountVerifyServiceMock,
            $applicationServiceMock,
            $companyInformationServiceMock,
            $recruitmentPostServiceMock,
            $resumeServiceMock,
        ]);

        $companyInformationServiceMock->expects($this->once())
            ->method('delete')
            ->willReturn(true);

        $recruitmentPostServiceMock->expects($this->once())
            ->method('delete')
            ->willReturn(true);

        $resumeServiceMock->expects($this->once())
            ->method('delete')
            ->willReturn(true);

        $applicationServiceMock->expects($this->once())
            ->method('delete')
            ->willReturn(true);

        $response = $usereServiceMock->delete([$user->id, $user2->id]);
        $this->assertEquals(2, $response);
    }
}
