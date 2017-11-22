<?php

namespace OliGriffiths\GUnit\PHPUnit\Constraint;

use Psr\Http\Message;

/**
 * BodyKeyExists constraint is used to validate a HTTP response body exists
 */
class BodyKeyExists extends BodyKey
{
    /**
     * BodyKeyEquals constructor.
     *
     * @param string $key The key to check for
     * @param bool $verbose True to enable verbose mode
     */
    public function __construct($key, $verbose = false)
    {
        parent::__construct($key, null, $verbose);
    }

    /**
     * Check if the body contains the key
     *
     * @param Message\ResponseInterface $response The response object
     * @return mixed
     */
    protected function matches($response)
    {
        return $this->hasBodyKey($response, $this->getKey());
    }

    public function toString()
    {
        return sprintf(' contains the key path: "%s"', $this->getKey());
    }
}
