<?php

namespace OliGriffiths\GUnit;

use GuzzleHttp;
use OliGriffiths\GUnit\Guzzle\Client;

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

        \PHPUnit_Util_Blacklist::$blacklistedClassNames[self::class] = 1;
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
        return Client::clientForTest($test, $this->guzzle_options, $this->base_uri, $this->headers);
    }
}
