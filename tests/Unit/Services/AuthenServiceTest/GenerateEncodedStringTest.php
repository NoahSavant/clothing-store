<?php

namespace Tests\Unit\Services\AuthenServiceTest;

use App\Services\BusinessServices\AuthenService;

class GenerateEncodedStringTest extends BaseAuthenServiceTest
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testSuccess()
    {
        $authenServiceMock = $this->getMockService(AuthenService::class, ['hash']);
        $authenServiceMock->expects($this->once())
            ->method('hash')
            ->willReturn('123456');
        $response = $authenServiceMock->generateEncodedString(now(), now(), 'testData');
        $this->assertIsString($response);
    }
}
