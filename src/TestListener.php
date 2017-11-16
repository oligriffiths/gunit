<?php

namespace OliGriffiths\GUnit;

use GuzzleHttp;

/**
 * Class TestListener
 */
class TestListener extends \PHPUnit_Framework_BaseTestListener
{
    /**
     * @var string A base URI string used to prefix all requests, defaults to http://localhost
     */
    private $base_uri;

    /**
     * @var array An optional array of headers passed with all requests
     */
    private $headers = [];

    /**
     * @var array Custom guzzle options
     */
    private $guzzle_options = [];

    /**
     * @var array The config options
     */
    private $config = [];

    /**
     * @var bool|mixed
     */
    private $verbose = false;

    private $auth_middleware;
    private $result_middleware;

    /**
     * @inheritDoc
     */
    public function __construct($config = [], array $authenticators = [], array $users = [])
    {
        if (is_string($config)) {
            $file = $config;
            if (!file_exists($file)) {
                throw new \InvalidArgumentException(sprintf('%s does not exist', $file));
            }
            $info = pathinfo($file, PATHINFO_EXTENSION);

            switch ($info) {
                case 'json':
                    $config = @json_decode($file, true);
                    if (!is_array($config)) {
                        throw new \InvalidArgumentException(sprintf('%s must be an array', $file));
                    }
                    break;

                case 'php':
                    $config = require $file;
                    if (!is_array($config)) {
                        throw new \InvalidArgumentException(sprintf('%s must return an array', $file));
                    }
                    break;

                default:
                    throw new \InvalidArgumentException('Acceptable file types are .json and .php');
            }

            if (isset($config['authenticators'])) {
                $users = $config['authenticators'];
            }
            
            if (isset($config['users'])) {
                $users = $config['users'];
            }
        }

        $this->base_uri = isset($config['base_uri']) ? $config['base_uri'] : 'http://localhost';
        $this->headers = isset($config['headers']) ? $config['headers'] : [];
        $this->verbose = isset($config['verbose']) ? $config['verbose'] : false;
        $this->guzzle_options = isset($config['guzzle_options']) ? $config['guzzle_options'] : [];
        $this->config = $config;
        $this->auth_middleware = new Middleware\Auth($authenticators, $users);
        $this->result_middleware = new Middleware\Result();
    }

    /**
     * Starts running the test suite and
     * @param \PHPUnit_Framework_TestSuite $suite
     */
    public function startTestSuite(\PHPUnit_Framework_TestSuite $suite)
    {
        // Initialize test options
        /** @var $test TestTrait|\PHPUnit_Framework_Test */
        foreach ($suite as $test) {
            $uses = class_uses($test);

            /** @var $test TestTrait|\PHPUnit_Framework_Test */
            if (!in_array(TestTrait::class, $uses, true)) {
                continue;
            }

            $test->setVerbose($this->verbose);
            $test->setClient($this->getClient($test));
        }
    }

    /**
     * @param \PHPUnit_Framework_Test $test
     */
    public function startTest(\PHPUnit_Framework_Test $test)
    {
        $this->test_options = [];

        $uses = class_uses($test);

        /** @var $test TestTrait|\PHPUnit_Framework_Test */
        if (in_array(TestTrait::class, $uses, true)) {

            $annotations = $test->getAnnotations();

            $auth_mode = isset($annotations['method']['auth-mode'][0]) ? $annotations['method']['auth-mode'][0] : null;
            if (!$auth_mode) {
                $auth_mode = isset($annotations['class']['auth-mode'][0]) ? $annotations['class']['auth-mode'][0] : null;
            }

            $auth_user = isset($annotations['method']['auth-user'][0]) ? $annotations['method']['auth-user'][0] : null;
            if (!$auth_user) {
                $auth_user = isset($annotations['class']['auth-user'][0]) ? $annotations['class']['auth-user'][0] : null;
            }
            
            $test->setTestAuth($auth_mode, $auth_user);
        }
    }

    /**
     * @param $test TestTrait|\PHPUnit_Framework_Test
     * @return GuzzleHttp\Client
     */
    public function getClient(\PHPUnit_Framework_Test $test)
    {
        $headers = array_merge($this->headers, $test->getHeaders());

        $options = array_merge(
            $this->guzzle_options,
            [
                'base_uri' => $test->getBaseUri() ?: $this->base_uri,
                'http_errors' => false,
                'headers' => $headers,
            ]
        );
        $options['handler'] = GuzzleHttp\HandlerStack::create();
        $options['handler']->unshift($this->result_middleware);
        $options['handler']->push($this->auth_middleware);

        return new GuzzleHttp\Client($options);
    }

//    /**
//     * @param GuzzleHttp\HandlerStack $stack
//     * @param string $type
//     * @param string|null $user
//     */
//    protected function addAuthHandlers(GuzzleHttp\HandlerStack $stack)
//    {
////        $stack->push($this->getOauth1Middleware());
//    }
//
////    /**
////     * @return Oauth\Oauth1
////     */
////    protected function getOauth1Middleware()
////    {
////        if (!$this->oauth1_middleware) {
////            $options = array_filter([
////                'consumer_key' => isset($this->auth['consumer_key']) ? $this->auth['consumer_key'] : null,
////                'consumer_secret' => isset($this->auth['consumer_secret']) ? $this->auth['consumer_secret'] : null,
////                'token' => isset($this->auth['token']) ? $this->auth['token'] : null,
////                'token_secret' => isset($this->auth['token_secret']) ? $this->auth['token_secret'] : null,
////            ]);
////
////            // Ensure Oauth1 installed
////            $class = "GuzzleHttp\\Subscriber\\Oauth\\Oauth1";
////            if (!class_exists($class)) {
////                throw new \LogicException('The class ' . $class . ' is missing, please install "guzzlehttp/oauth-subscriber"');
////            }
////
////            $this->oauth1_middleware = new Oauth\Oauth1($options);
////        }
////
////        return $this->oauth1_middleware;
////    }
//
//    /**
//     * Sets the test auth user creds within the oauth middleware
//     *
//     * @param string|null $user
//     */
//    protected function setUser($user = null)
//    {
////        if ($user && !isset($this->auth['users'][$user])) {
////            throw new \InvalidArgumentException(sprintf('User "%s" undefined', $user));
////        }
////
////        $details = isset($this->auth['users'][$user]) ? $this->auth['users'][$user] : [];
////
////        // Set oauth1 details
////        $oauth1 = $this->getOauth1Middleware();
////        $reflection = new \ReflectionClass(get_class($oauth1));
////        $property = $reflection->getProperty('config');
////        $property->setAccessible(true);
////
////        $value = $property->getValue($oauth1);
////        $property->setValue($oauth1, array_merge($value, $details));
//    }
}
