<?php

use \OliGriffiths\GUnit\Guzzle\Result;

class SomeTest extends PHPUnit_Framework_TestCase
{
    use \OliGriffiths\GUnit\TestTrait;

    /**
     * The result of this test will be shared between any other test that depends on it, and not re-run the test
     *
     * @return \OliGriffiths\GUnit\Guzzle\Result
     */
    public function testRequest201()
    {
        return $this->makeRequest('GET', '/201');
    }

    /**
     * @param Result $result The result of executing testRequest201
     * @depends testRequest201
     */
    public function testSomething(Result $result)
    {
        $this->assertStatusCode(201, $result);
        $this->assertHeaderExists('Content-Type', $result);
        $this->assertHeaderEquals('Content-Type', 'text/plain; charset=utf-8', $result);
    }

    /**
     * @param Result $result The result of executing testRequest201
     * @depends testRequest201
     */
    public function testBody(Result $result)
    {
        $this->assertBodyEquals('201 Created', $result);
    }

    public function testJsonResponse()
    {
        $request = new \GuzzleHttp\Psr7\Request(
            'GET',
            'https://httpstat.us/200'
        );

        $response = new \GuzzleHttp\Psr7\Response(
            200,
            ['Content-Type' => 'application/json'],
            '{"foo":{"bar":["baz"]}}'
        );

        $result = new Result($request, $response);

        $this->assertBodyKeyExists('foo.bar', $result);
        $this->assertBodyKeyEquals('foo.bar.0', 'baz', $result);
        $this->assertBodyEquals([
            'foo' => [
                'bar' => ['baz']
            ]
        ], $result);
    }
}
