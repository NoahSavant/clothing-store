<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BaseTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getMockService($class, $method = [], $contructs = [])
    {
        if ($contructs === []) {
            return $this->getMockBuilder($class)
                ->disableOriginalConstructor()
                ->onlyMethods($method)
                ->getMock();
        }

        return $this->getMockBuilder($class)
            ->setConstructorArgs($contructs)
            ->onlyMethods($method)
            ->getMock();
    }

    protected function getObject($array)
    {
        $object = new BaseObjectTest();
        foreach ($array as $key => $value) {
            if (is_string($value)) {
                $object->$key = $value;
            } else {
                $object->$key = $this->getObject($value);
            }
        }

        return $object;
    }
}
