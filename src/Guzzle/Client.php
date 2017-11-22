<?php

namespace OliGriffiths\GUnit\Guzzle;

use OliGriffiths\GUnit;
use GuzzleHttp;

/**
 * Guzzle Client that auto registers the result middleware which is required for the test harness to work
 */
class Client extends GuzzleHttp\Client
{
    /**
     * @inheritDoc
     */
    public function __construct(array $config = [])
    {
        $config['http_errors'] = false;
        $config['handler'] = GuzzleHttp\HandlerStack::create();
        $config['handler']->unshift(new GUnit\Middleware\Result());

        parent::__construct($config);
    }

    /**
     * @param $test GUnit\TestTrait|\PHPUnit_Framework_Test
     * @param array $guzzle_config Optional guzzle config
     * @param string $base_uri Optional base URI to pass to guzzle, all requests will default to this base
     * @param array $headers Optional array of headers to be sent with every request
     * @return GuzzleHttp\Client
     */
    public static function clientForTest(
        \PHPUnit_Framework_Test $test,
        array $guzzle_config = [],
        $base_uri = null,
        array $headers = []
    ) {
        $uses = class_uses($test);

        /** @var $test GUnit\TestTrait|\PHPUnit_Framework_Test */
        if (in_array(GUnit\TestTrait::class, $uses, true)) {
            $headers = array_merge($headers, $test->getHeaders());
            $base_uri = $test->getBaseUri() ?: $base_uri;
        }

        $config = array_merge(
            $guzzle_config,
            [
                'base_uri' => $base_uri,
                'headers' => $headers,
            ]
        );

        return new static($config);
    }
}
