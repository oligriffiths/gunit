<?php

namespace OliGriffiths\GUnit;

use Psr\Http\Message;
use GuzzleHttp;
use GuzzleHttp\Subscriber\Oauth;

/**
 * Class TestCase
 * @package Tumblr\ApiTest
 */
abstract class TestCase extends \PHPUnit_Framework_TestCase
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
     * @var array
     */
    private $auth = [];

    /**
     * @var string
     */
    private $test_auth_user;

    /**
     * @var string
     */
    private $test_auth_mode;

    /**
     * @var GuzzleHttp\Client
     */
    private $client;

    /**
     * @var Message\ResponseInterface
     */
    private $last_response;

    /**
     * @var bool In verbose mode, full response payloads are printed
     */
    private $verbose;

    /**
     * @var
     */
    private $oauth1_middleware;

    /**
     * @return string
     */
    public function getBaseUri()
    {
        return $this->base_uri;
    }

    /**
     * @param string $base_uri
     */
    public function setBaseUri($base_uri)
    {
        $this->base_uri = $base_uri;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param array $headers
     */
    public function setHeaders(array $headers)
    {
        $this->headers = $headers;
    }

    /**
     * @return array
     */
    public function getGuzzleOptions()
    {
        return $this->guzzle_options;
    }

    /**
     * @param array $guzzle_options
     */
    public function setGuzzleOptions(array $guzzle_options)
    {
        $this->guzzle_options = $guzzle_options;
    }

    /**
     * @return array
     */
    public function getAuth()
    {
        return $this->auth;
    }

    /**
     * Sets the authentication array of users
     *
     * @param array $auth
     */
    public function setAuth(array $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Tests the specific user to be for the current test, should be an index within $this->auth
     *
     * @param array $user The test auth user
     */
    public function setTestAuthUser($user = null)
    {
        $this->test_auth_user = $user;
        $this->setUser($user);
    }

    /**
     * @return string
     */
    public function getTestAuthMode()
    {
        return $this->test_auth_mode;
    }

    /**
     * @param string $test_auth_mode
     */
    public function setTestAuthMode($test_auth_mode = null)
    {
        $this->test_auth_mode = $test_auth_mode;
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
    protected function assertOK(Message\ResponseInterface $response = null)
    {
        $this->assertStatusCode(200, $response);
    }

    /**
     * @param Message\ResponseInterface|null $response
     */
    protected function assertCreated(Message\ResponseInterface $response = null)
    {
        $this->assertStatusCode(201, $response);
    }

    /**
     * @param Message\ResponseInterface|null $response
     */
    protected function assertBadRequest(Message\ResponseInterface $response = null)
    {
        $this->assertStatusCode(400, $response);
    }

    /**
     * @param Message\ResponseInterface|null $response
     */
    protected function assertUnauthorized(Message\ResponseInterface $response = null)
    {
        $this->assertStatusCode(401, $response);
    }

    /**
     * @param Message\ResponseInterface|null $response
     */
    protected function assertForbidden(Message\ResponseInterface $response = null)
    {
        $this->assertStatusCode(403, $response);
    }

    /**
     * @param Message\ResponseInterface|null $response
     */
    protected function assertNotFound(Message\ResponseInterface $response = null)
    {
        $this->assertStatusCode(404, $response);
    }

    /**
     * @param $expected
     * @param Message\ResponseInterface|null $response
     */
    protected function assertStatusCode($expected, Message\ResponseInterface $response = null)
    {
        $response = $response ?: $this->last_response;

        $actual = $response->getStatusCode();
        $this->assertEquals($expected, $actual, $this->formatMessage(
            $response,
            'Expected status code %d, received %d',
            $expected,
            $actual
        ));
    }

    /**
     * @param $header
     * @param Message\ResponseInterface|null $response
     */
    protected function assertHeaderExists($header, Message\ResponseInterface $response = null)
    {
        $response = $response ?: $this->last_response;

        $this->assertTrue($response->hasHeader($header), $this->formatMessage(
            $response,
            'Header "%s" missing',
            $header
        ));
    }

    /**
     * @param string $header
     * @param string $expected
     * @param Message\ResponseInterface|null $response
     */
    protected function assertHeaderEquals($header, $expected, Message\ResponseInterface $response = null)
    {
        $response = $response ?: $this->last_response;

        $actual = $response->getHeaderLine($header);
        $this->assertEquals($expected, $actual, $this->formatMessage(
            $response,
            'Header "%s" expected "%s", received "%s"',
            $header,
            $expected,
            $actual
        ));
    }

    /**
     * @param string $key
     * @param Message\ResponseInterface|null $response
     */
    protected function assertBodyKeyExists($key, Message\ResponseInterface $response = null)
    {
        $response = $response ?: $this->last_response;

        $has_key = true;
        try {
            $this->getBodyKey($key, $response);
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
    protected function assertBodyKeyEquals($key, $expected, Message\ResponseInterface $response = null)
    {
        $response = $response ?: $this->last_response;

        $has_key = true;
        $actual = null;
        try {
            $actual = $this->getBodyKey($key, $response);
            $this->assertEquals($expected, $actual, $this->formatMessage(
                $response,
                'Expected body key "%s" with value "%s", actual %s"',
                $key,
                $expected,
                is_scalar($actual) ? $actual : print_r($actual, true)
            ));
        } catch (\UnexpectedValueException $e) {
            $this->assertTrue($has_key, $this->formatMessage($response, 'Expected body key "%s" missing', $key));
        }
    }

    /**
     * @param string $key
     * @param Message\ResponseInterface $response
     * @return mixed
     */
    protected function getBodyKey($key, Message\ResponseInterface $response)
    {
        $body = $this->decodeBody($response);
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
     * @param Message\ResponseInterface $response
     * @param bool $assoc
     * @return mixed
     */
    protected function decodeBody(Message\ResponseInterface $response, $assoc = true)
    {
        $content_type = explode(';', $response->getHeaderLine('Content-Type'));
        $content_type = $content_type[0];

        if (preg_match('#application\/.*\+json#', $content_type)) {
            $content_type = 'application/json';
        }

        switch ($content_type) {
            case 'application/json':
                return json_decode($response->getBody(), $assoc);

            case 'application/x-www-form-urlencoded':
                parse_str($response->getBody(), $result);
                return $result;

            default:
                throw new \UnexpectedValueException(sprintf(
                    'Unable to decode body, content type %s not supported',
                    $content_type
                ));
        }
    }

    /**
     * @param Message\ResponseInterface $response
     * @param string $message
     * @param array ...$params
     * @return mixed|string
     */
    private function formatMessage(Message\ResponseInterface $response, $message, ...$params)
    {
        // Prepend message as first argument
        array_unshift($params, $message);
        $message = call_user_func_array('sprintf', $params);

        /** @var Message\RequestInterface $request */
        $request = $response->request;

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
     * @param Message\ResponseInterface $response
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
    protected function request($method, $uri = null, array $options = [])
    {
        return $this->getClient()->request($method, $uri, $options);
    }

    /**
     * @param Message\RequestInterface $request
     * @param array $options
     * @return Message\ResponseInterface
     */
    protected function send(\Psr\Http\Message\RequestInterface $request, array $options = [])
    {
        return $this->getClient()->send($request, $options);
    }

    /**
     * @inheritDoc
     */
    protected function tearDown()
    {
        parent::tearDown();
        $this->last_response = null;
    }

    /**
     * @return GuzzleHttp\Client
     */
    public function getClient()
    {
        if (!$this->client) {
            $options = array_merge(
                $this->guzzle_options,
                [
                    'base_uri' => $this->base_uri,
                    'http_errors' => false,
                    'headers' => $this->headers,
                ]
            );
            $options['handler'] = GuzzleHttp\HandlerStack::create();
            $options['handler']->push($this);
            $this->addAuthHandlers($options['handler']);

            $this->client = new GuzzleHttp\Client($options);
        }

        return $this->client;
    }

    /**
     * @param GuzzleHttp\HandlerStack $stack
     * @param string $type
     * @param string|null $user
     */
    protected function addAuthHandlers(GuzzleHttp\HandlerStack $stack)
    {
        $stack->push($this->getOauth1Middleware());
    }

    /**
     * @return Oauth\Oauth1
     */
    protected function getOauth1Middleware()
    {
        if (!$this->oauth1_middleware) {
            $options = array_filter([
                'consumer_key' => isset($this->auth['consumer_key']) ? $this->auth['consumer_key'] : null,
                'consumer_secret' => isset($this->auth['consumer_secret']) ? $this->auth['consumer_secret'] : null,
                'token' => isset($this->auth['token']) ? $this->auth['token'] : null,
                'token_secret' => isset($this->auth['token_secret']) ? $this->auth['token_secret'] : null,
            ]);

            // Ensure Oauth1 installed
            $class = "GuzzleHttp\\Subscriber\\Oauth\\Oauth1";
            if (!class_exists($class)) {
                throw new \LogicException('The class ' . $class . ' is missing, please install "guzzlehttp/oauth-subscriber"');
            }

            $this->oauth1_middleware = new Oauth\Oauth1($options);
        }

        return $this->oauth1_middleware;
    }

    /**
     * Sets the test auth user creds within the oauth middleware
     *
     * @param string|null $user
     */
    protected function setUser($user = null)
    {
        if ($user && !isset($this->auth['users'][$user])) {
            throw new \InvalidArgumentException(sprintf('User "%s" undefined', $user));
        }

        $details = isset($this->auth['users'][$user]) ? $this->auth['users'][$user] : [];

        // Set oauth1 details
        $oauth1 = $this->getOauth1Middleware();
        $reflection = new \ReflectionClass(get_class($oauth1));
        $property = $reflection->getProperty('config');
        $property->setAccessible(true);

        $value = $property->getValue($oauth1);
        $property->setValue($oauth1, array_merge($value, $details));
    }

    /**
     * Called when the middleware is handled.
     *
     * @param callable $handler The previous handler
     *
     * @return \Closure
     */
    public function __invoke(callable $handler)
    {
        return function(Message\RequestInterface $request, array $options) use ($handler) {

            $options['auth'] = $this->getTestAuthMode();

            // Execute next handler
            $promise = $handler($request, $options);

            // Get response
            $response = $promise->wait();

            // Store for use in assertions
            $this->last_response = $response;

            // Add request so it can be extracted during test assertions
            $response->request = $request;
            return $promise;
        };
    }
}
