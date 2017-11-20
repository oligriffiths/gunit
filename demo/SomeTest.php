<?php

class SomeTest extends PHPUnit_Framework_TestCase
{
    use \OliGriffiths\GUnit\TestTrait;

    public function testSomething()
    {
        $this->makeRequest('GET', '/201');
        $this->assertStatusCode(201);
        $this->assertBodyEquals('201 Created');
        $this->assertHeaderExists('Content-Type');
        $this->assertHeaderEquals('Content-Type', 'text/plain; charset=utf-8');
    }
}
