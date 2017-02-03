<?php

namespace OliGriffiths\GUnit;

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
     * @var array Array of auth user keys
     */
    private $auth;

    /**
     * @var array The config options
     */
    private $config = [];

    /**
     * @var bool|mixed
     */
    private $verbose = false;

    /**
     * @inheritDoc
     */
    public function __construct($config = [])
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
        }

        $this->base_uri = isset($config['base_uri']) ? $config['base_uri'] : 'http://localhost';
        $this->headers = isset($config['headers']) ? $config['headers'] : [];
        $this->auth = isset($config['auth']) ? $config['auth'] : [];
        $this->verbose = isset($config['verbose']) ? $config['verbose'] : false;
        $this->config = $config;
    }

    /**
     * Starts running the test suite and
     * @param \PHPUnit_Framework_TestSuite $suite
     */
    public function startTestSuite(\PHPUnit_Framework_TestSuite $suite)
    {
        // Initialize test options
        foreach ($suite as $test) {
            if (!$test instanceof TestCase) {
                continue;
            }

            if ($this->base_uri && !$test->getBaseUri()) {
                $test->setBaseUri($this->base_uri);
            }

            $headers = array_merge($this->headers, $test->getHeaders());
            $test->setHeaders($headers);

            $config = array_merge($this->guzzle_options, $test->getGuzzleOptions());
            $test->setGuzzleOptions($config);

            $test->setAuth($this->auth);
        }
    }

    /**
     * @param \PHPUnit_Framework_Test $test
     */
    public function startTest(\PHPUnit_Framework_Test $test)
    {
        $this->test_options = [];

        if ($test instanceof TestCase) {
            $test->setVerbose($this->verbose);

            $annotations = $test->getAnnotations();

            $auth_user = isset($annotations['class']['auth-user'][0]) ? $annotations['class']['auth-user'][0] : null;
            if (!$auth_user) {
                $auth_user = isset($annotations['method']['auth-user'][0]) ? $annotations['method']['auth-user'][0] : null;
            }

            $auth_mode = isset($annotations['class']['auth-mode'][0]) ? $annotations['class']['auth-mode'][0] : null;
            if (!$auth_mode) {
                $auth_mode = isset($annotations['method']['auth-mode'][0]) ? $annotations['method']['auth-mode'][0] : null;
            }

            $test->setTestAuthMode($auth_mode);
            $test->setTestAuthUser($auth_user);
        }
    }

}
