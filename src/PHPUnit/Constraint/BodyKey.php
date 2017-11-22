<?php

namespace OliGriffiths\GUnit\PHPUnit\Constraint;

use Psr\Http\Message;

/**
 * BodyKey constraint is used to validate a HTTP response body as an array
 */
abstract class BodyKey extends Body
{
    /**
     * @var string
     */
    private $key;

    /**
     * BodyKey constructor.
     *
     * @param string $key The key to check for
     * @param mixed $expected The expected value
     * @param bool $verbose True to enable verbose mode
     */
    public function __construct($key, $expected, $verbose = false)
    {
        parent::__construct($expected, $verbose);

        $this->key = $key;
    }

    /**
     * @return mixed
     */
    protected function getKey()
    {
        return $this->key;
    }

    /**
     * Check if a key exists within the decoded body.
     * Key uses dot notation to traverse as many levels deep as necessary. E.g.
     *
     * foo.bar.baz = Will check for existence of ['foo' => ['bar' => ['baz' => '']]] => true
     * foo.0.bar = Will check for existence of ['foo' => [0' => ['baz' => '']]] => true
     * foo.bar.baz = Will check for existence of ['foo' => ['bar' => ['bat' => '']]] => false
     *
     * @param Message\ResponseInterface $response The guzzle response object
     * @param string $key The key to extract
     * @return mixed
     */
    protected function hasBodyKey(Message\ResponseInterface $response, $key)
    {
        $body = $this->decodeBody($response->getHeaderLine('Content-Type'), $response->getBody());

        $parts = explode('.', $key);
        foreach ($parts as $part) {
            if (!is_array($body) || !array_key_exists($part, $body)) {
                return false;
            }

            $body = $body[$part];
        }

        return true;
    }

    /**
     * Check if a key exists within the decoded body.
     * Key uses dot notation to traverse as many levels deep as necessary. E.g.
     *
     * foo.bar.baz = Will check value ['foo' => ['bar' => ['baz' => 'bat']]] === 'bar'
     * foo.0.bar = Will check value ['foo' => [0' => ['baz' => 'bat']]] === 'bat'
     * foo.bar.baz = Will check value of ['foo' => ['bar' => ['bat' => '']]] => throws UnexpectedValueException
     *
     * @param Message\ResponseInterface $response The guzzle response object
     * @param string $key The key to extract
     * @return mixed
     */
    protected function getBodyKey(Message\ResponseInterface $response, $key)
    {
        $body = $this->decodeBody($response->getHeaderLine('Content-Type'), $response->getBody());

        $parts = explode('.', $key);
        foreach ($parts as $part) {
            if (!is_array($body) || !array_key_exists($part, $body)) {
                throw new \UnexpectedValueException('Body key missing');
            }

            $body = $body[$part];
        }

        return $body;
    }
}
