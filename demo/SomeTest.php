<?php

class SomeTest extends PHPUnit_Framework_TestCase
{
    use \OliGriffiths\GUnit\TestTrait;

    public function testSomething()
    {
        $this->makeRequest('GET', '/201');
        $this->assertStatusCode(200);
        $this->assertBodyEquals('201 Created');
    }
}
