<?php

namespace OliGriffiths\GUnit;

use Psr\Http\Message;
use GuzzleHttp;
use GuzzleHttp\Subscriber\Oauth;

/**
 * Class TestCase
 * @package Tumblr\ApiTest
 */
trait GUnitTrait
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
     * @var array
     */
    protected $guzzle_options = [];

    /**
     * @var GuzzleHttp\Client
     */
    private $client;

    /**
     * @var GuzzleResult
     */
    private $last_result;

    /**
     * @var bool In verbose mode, full response payloads are printed
     */
    private $verbose;

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
        return $this->guzzle_options;
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
     * @param Message\ResponseInterface|null $response
     */
    protected function assertOK(GuzzleResult $result = null)
    {
        $this->assertStatusCode(200, $result);
    }

    /**
     * @param Message\ResponseInterface|null $response
     */
    protected function assertCreated(GuzzleResult $result = null)
    {
        $this->assertStatusCode(201, $result);
    }

    /**
     * @param Message\ResponseInterface|null $response
     */
    protected function assertBadRequest(GuzzleResult $result = null)
    {
        $this->assertStatusCode(400, $result);
    }

    /**
     * @param Message\ResponseInterface|null $response
     */
    protected function assertUnauthorized(GuzzleResult $result = null)
    {
        $this->assertStatusCode(401, $result);
    }

    /**
     * @param Message\ResponseInterface|null $response
     */
    protected function assertForbidden(GuzzleResult $result = null)
    {
        $this->assertStatusCode(403, $result);
    }

    /**
     * @param Message\ResponseInterface|null $response
     */
    protected function assertNotFound(GuzzleResult $result = null)
    {
        $this->assertStatusCode(404, $result);
    }

    /**
     * @param $expected
     * @param Message\ResponseInterface|null $response
     */
    protected function assertStatusCode($expected, GuzzleResult $result = null)
    {
        $result = $result ?: $this->last_result;

        $actual = $result->getResponse()->getStatusCode();
        $this->assertEquals($expected, $actual, $this->formatMessage(
            $result,
            'Expected status code %d, received %d',
            $expected,
            $actual
        ));
    }

    /**
     * @param $header
     * @param Message\ResponseInterface|null $response
     */
    protected function assertHeaderExists($header, GuzzleResult $result = null)
    {
        $result = $result ?: $this->last_result;

        $this->assertTrue($result->getResponse()->hasHeader($header), $this->formatMessage(
            $result,
            'Header "%s" missing',
            $header
        ));
    }

    /**
     * @param string $header
     * @param string $expected
     * @param Message\ResponseInterface|null $response
     */
    protected function assertHeaderEquals($header, $expected, GuzzleResult $result = null)
    {
        $result = $result ?: $this->last_result;

        $actual = $result->getResponse()->getHeaderLine($header);
        $this->assertEquals($expected, $actual, $this->formatMessage(
            $result,
            'Header "%s" expected "%s", received "%s"',
            $header,
            $expected,
            $actual
        ));
    }

    /**
     * @param string $header
     * @param string $expected
     * @param Message\ResponseInterface|null $response
     */
    protected function assertContentType($expected, GuzzleResult $result = null)
    {
        $result = $result ?: $this->last_result;

        $actual = $result->getResponse()->getHeaderLine('Content-Type');
        $this->assertEquals($expected, $actual, $this->formatMessage(
            $result,
            'Header "Content-Type" expected "%s", received "%s"',
            $expected,
            $actual
        ));
    }

    /**
     * @param string $key
     * @param Message\ResponseInterface|null $response
     */
    protected function assertBodyKeyExists($key, GuzzleResult $result = null)
    {
        $result = $result ?: $this->last_result;

        $has_key = true;
        try {
            $this->getBodyKey($key, $result->getResponse());
        } catch (\UnexpectedValueException $e) {
            $has_key = false;
        }

        $this->assertTrue($has_key, $this->formatMessage(
            $response,
            'Expected body key "%s" missing',
            $key
        ));
    }

    /**
     * @param string $key
     * @param $expected
     * @param Message\ResponseInterface|null $response
     */
    protected function assertBodyKeyEquals($key, $expected, GuzzleResult $result = null)
    {
        $result = $result ?: $this->last_result;

        $has_key = true;
        $actual = null;
        try {
            $actual = $this->getBodyKey($key, $result->getResponse());
            $this->assertEquals($expected, $actual, $this->formatMessage(
                $result,
                'Expected body key "%s" with value "%s", actual %s"',
                $key,
                $expected,
                is_scalar($actual) ? $actual : print_r($actual, true)
            ));
        } catch (\UnexpectedValueException $e) {
            $this->assertTrue($has_key, $this->formatMessage($result, 'Expected body key "%s" missing', $key));
        }
    }

    /**
     * @param string $key
     * @param GuzzleResult $result
     * @return mixed
     */
    protected function getBodyKey($key, GuzzleResult $result)
    {
        $body = $this->decodeBody($result->getResponse());
        $parts = explode('.', $key);
        foreach ($parts as $part) {
            if (!array_key_exists($part, $body)) {
                throw new \UnexpectedValueException('Body key missing');
            }

            $body = $body[$part];
        }

        return $body;
    }

    /**
     * @param GuzzleResult $result
     * @return mixed
     */
    protected function decodeBody(Message\ResponseInterface $response)
    {
        $content_type = explode(';', $response->getHeaderLine('Content-Type'));
        $content_type = $content_type[0];

        if (preg_match('#application\/.*\+json#', $content_type)) {
            $content_type = 'application/json';
        }

        return $this->decodeBodyData($content_type, $response->getBody());
    }

    /**
     * @param string $content_type The response content type
     * @param string $data The response data
     * @return mixed
     * @throws \UnexpectedValueException If the content type is unsupported
     */
    protected function decodeBodyData($content_type, $data)
    {
        switch ($content_type) {
            case 'application/json':
                return json_decode($data, true);

            case 'application/x-www-form-urlencoded':
                parse_str($data, $result);
                return $result;

            default:
                throw new \UnexpectedValueException(sprintf(
                    'Unable to decode body, content type %s not supported',
                    $content_type
                ));
        }
    }

    /**
     * @param GuzzleResult $response
     * @param string $message
     * @param array ...$params
     * @return mixed|string
     */
    private function formatMessage(GuzzleResult $result, $message, ...$params)
    {
        $request = $result->getRequest();
        $response = $result->getResponse();
        
        // Prepend message as first argument
        array_unshift($params, $message);
        $message = call_user_func_array('sprintf', $params);

        // Add URI
        $message .= ' - URI: ' . $request->getUri();

        // & optional response body
        if ($this->verbose) {
            $message .= PHP_EOL . 'Response:' . PHP_EOL;
            $message .= $this->responeToString($response);
        }

        return $message;
    }

    /**
     * @param GuzzleResult $result
     * @return string
     */
    private function responeToString(Message\ResponseInterface $response)
    {
        $output = [
            sprintf(
                'HTTP/%s %d %s',
                $response->getProtocolVersion(),
                $response->getStatusCode(),
                $response->getReasonPhrase()
            ),
            '',
        ];

        foreach ($response->getHeaders() as $header => $values) {
            foreach ($values as $value) {
                $output[] = $header . ': ' . $value;
            }
        }

        $output[] = '';
        $content_type = explode(';', $response->getHeaderLine('Content-Type'));
        $content_type = $content_type[0];

        if (preg_match('#application\/.*\+json#', $content_type)) {
            $content_type = 'application/json';
        }

        switch ($content_type) {
            case 'application/json':
                $output[] = json_encode(json_decode($response->getBody(), true), JSON_PRETTY_PRINT);
                break;
            default:
                $output[] = $response->getBody();
                break;
        }

        return implode(PHP_EOL, $output);
    }

    /**
     * @param $method
     * @param null $uri
     * @param array $options
     * @return Message\ResponseInterface
     */
    protected function makeRequest($method, $uri = null, array $options = [])
    {
        $this->last_result = $this->getClient()->request($method, $uri, $options);
        return $this->last_result;
    }

    /**
     * @param Message\RequestInterface $request
     * @param array $options
     * @return Message\ResponseInterface
     */
    protected function sendRequest(\Psr\Http\Message\RequestInterface $request, array $options = [])
    {
        $this->last_result = $this->getClient()->send($request, $options);
        return $this->last_result;
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
        return $this->client;
    }

    /**
     * @param GuzzleHttp\Client $client
     */
    public function setClient(GuzzleHttp\Client $client)
    {
        $this->client = $client;
    }
}
