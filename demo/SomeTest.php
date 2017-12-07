<?php

use \OliGriffiths\GUnit\Guzzle\Result;

class SomeTest extends PHPUnit_Framework_TestCase
{
    use \OliGriffiths\GUnit\TestTrait;

    /**
     * @param Result $result The result of executing testRequest201
     * @depends testRequest201
     */
    public function testSomething(Result $result)
    {
        $result = $this->makeRequest('GET', '/201');
        $this->assertStatusCode(201, $result);
        $this->assertHeaderExists('Content-Type', $result);
        $this->assertHeaderEquals('Content-Type', 'text/plain; charset=utf-8', $result);
    }

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
     * This test receives the $result from testRequest201() as the first argument, allowing multiple tests to
     * utilize the same result without re-requesting it.
     *
     * @param Result $result The result of executing testRequest201
     * @depends testRequest201
     */
    public function testBody(Result $result)
    {
        $this->assertBodyEquals('201 Created', $result);
    }

    /**
     * This test shows how to test for body keys in the decoded JSON response. This doesn't actually make an HTTP
     * request as httpstat.us doesn't return a key/value JSON response unfortunately, so we're mocking one to show
     * how the body assert key methods work
     */
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
