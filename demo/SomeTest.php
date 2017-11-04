<?php

class SomeTest extends PHPUnit_Framework_TestCase
{
    use \OliGriffiths\GUnit\GUnitTrait;


    public function testSomething()
    {
        $this->makeRequest('GET', 'https://httpstatuses.com/201');
        $this->assertStatusCode(201);
    }
}
