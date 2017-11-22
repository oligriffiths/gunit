<?php

namespace OliGriffiths\GUnit;

use OliGriffiths\GUnit\Guzzle;
use OliGriffiths\GUnit\PHPUnit\Constraint;
use Psr\Http\Message;
use GuzzleHttp;

/**
 * Class TestCase
 * @package Tumblr\ApiTest
 */
trait TestTrait
{
    /**
     * @var string
     */
    protected $base_uri;

    /**
     * @var array
     */
    protected $headers = [];

    /**
     * @var GuzzleHttp\Client
     */
    private $client;

    /**
     * @var Guzzle\Result
     */
    private $last_result;

    /**
     * @var bool In verbose mode, full response payloads are printed
     */
    private $verbose;
    
    private $test_auth_mode;
    private $test_auth_user;

    /**
     * @return string
     */
    public function getBaseUri()
    {
        return $this->base_uri;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @return array
     */
    public function getGuzzleOptions()
    {
        return [];
    }

    /**
     * @return boolean
     */
    public function isVerbose()
    {
        return $this->verbose;
    }

    /**
     * @param boolean $verbose
     */
    public function setVerbose($verbose)
    {
        $this->verbose = $verbose;
    }

    /**
     * @param Guzzle\Result $result
     */
    public function assertOK(Guzzle\Result $result, $message = null)
    {
        $this->assertStatusCode(200, $result, $message);
    }

    /**
     * @param Guzzle\Result $result
     */
    public function assertCreated(Guzzle\Result $result, $message = null)
    {
        $this->assertStatusCode(201, $result, $message);
    }

    /**
     * @param Guzzle\Result $result
     */
    public function assertBadRequest(Guzzle\Result $result, $message = null)
    {
        $this->assertStatusCode(400, $result, $message);
    }

    /**
     * @param Guzzle\Result $result
     */
    public function assertUnauthorized(Guzzle\Result $result, $message = null)
    {
        $this->assertStatusCode(401, $result, $message);
    }

    /**
     * @param Guzzle\Result $result
     */
    public function assertForbidden(Guzzle\Result $result, $message = null)
    {
        $this->assertStatusCode(403, $result, $message);
    }

    /**
     * @param Guzzle\Result $result
     */
    public function assertNotFound(Guzzle\Result $result, $message = null)
    {
        $this->assertStatusCode(404, $result, $message);
    }

    /**
     * @param $expected
     * @param Guzzle\Result $result
     */
    public function assertStatusCode($expected, Guzzle\Result $result, $message = null)
    {
        $result = $result ?: $this->last_result;

        static::assertThat($result, new Constraint\StatusCode($expected, $this->isVerbose()), $message);
    }

    /**
     * @param $header
     * @param Guzzle\Result $result
     */
    public function assertHeaderExists($header, Guzzle\Result $result, $message = null)
    {
        static::assertThat($result, new Constraint\HeaderExists($header, $this->isVerbose()), $message);
    }

    /**
     * @param string $header
     * @param string|array $expected
     * @param Guzzle\Result $result
     */
    public function assertHeaderEquals($header, $expected, Guzzle\Result $result, $message = null)
    {
        static::assertThat($result, new Constraint\HeaderEquals($header, $expected, $this->isVerbose()), $message);
    }

    /**
     * @param string $header
     * @param string $expected
     * @param Guzzle\Result $result
     */
    public function assertContentType($expected, Guzzle\Result $result, $message = null)
    {
        $this->assertHeaderEquals('Content-Type', $expected, $result, $message);
    }

    /**
     * @param $expected
     * @param Guzzle\Result|null $result
     */
    public function assertBodyEquals($expected, Guzzle\Result $result, $message = null)
    {
        $result = $result ?: $this->last_result;

        static::assertThat($result, new Constraint\BodyEquals($expected, true, $this->isVerbose()), $message);
    }

    /**
     * @param $expected
     * @param Guzzle\Result|null $result
     */
    public function assertBodyContains($expected, Guzzle\Result $result, $message = null)
    {
        $result = $result ?: $this->last_result;
        $body = (string) $result->getResponse()->getBody();

        $this->assertContains($expected, $body, $this->formatMessage(
            $result,
            'Expected body to contain "%s"',
            $expected
        ));
    }

    /**
     * @param string $key
     * @param Guzzle\Result $result
     */
    public function assertBodyKeyExists($key, Guzzle\Result $result, $message = null)
    {
        static::assertThat($result, new Constraint\BodyKeyExists($key, $this->isVerbose()), $message);
    }

    /**
     * @param string $key
     * @param $expected
     * @param Guzzle\Result $result
     */
    public function assertBodyKeyEquals($key, $expected, Guzzle\Result $result, $message = null)
    {
        static::assertThat($result, new Constraint\BodyKeyEquals($key, $expected, $this->isVerbose()), $message);
    }

    /**
     * @param $method
     * @param null $uri
     * @param array $headers
     * @param array $options
     * @return Guzzle\Result
     */
    protected function makeRequest($method, $uri = null, array $headers = [], array $options = [])
    {
        $options += $this->getGuzzleAuth();

        if(!empty($headers)) {
            $options['headers'] = $headers;
        }

        $this->last_result = $this->getClient()->request($method, $uri, $options);
        return $this->last_result;
    }

    /**
     * @param Message\RequestInterface $request
     * @param array $headers
     * @param array $options
     * @return Guzzle\Result
     */
    protected function sendRequest(Message\RequestInterface $request, array $headers = [], array $options = [])
    {
        $options += $this->getGuzzleAuth();

        if(!empty($headers)) {
            $options['headers'] = $headers;
        }

        $this->last_result = $this->getClient()->send($request, $options);
        return $this->last_result;
    }

    /**
     * @return array
     */
    protected function getGuzzleAuth()
    {
        return [
            'auth_mode' => $this->test_auth_mode,
            'auth_user' => $this->test_auth_user,
        ];
    }

    /**
     * @inheritDoc
     */
    protected function tearDown()
    {
        parent::tearDown();
        $this->last_result = null;
    }

    /**
     * @return GuzzleHttp\Client
     */
    public function getClient()
    {
        if (!$this->client) {
            $this->setClient(Guzzle\Client::clientForTest($this));
        }

        return $this->client;
    }

    /**
     * @param GuzzleHttp\Client $client
     */
    public function setClient(GuzzleHttp\Client $client)
    {
        $this->client = $client;
    }

    /**
     * @param $mode
     * @param $user
     */
    public function setTestAuth($mode, $user)
    {
        $this->test_auth_mode = $mode;
        $this->test_auth_user = $user;
    }
}
