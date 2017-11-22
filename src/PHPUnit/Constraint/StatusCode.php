<?php

namespace OliGriffiths\GUnit\PHPUnit\Constraint;

use OliGriffiths\GUnit\Guzzle;

/**
 * StatusCode constraint is used to validate an HTTP status code
 */
class StatusCode extends AbstractConstraint
{
    /**
     * StatusCode constructor, sets the name
     *
     * @param mixed $expected The expected status code to compare against
     * @param bool $verbose True if enabling verbose mode
     */
    public function __construct($expected, $verbose = false)
    {
        parent::__construct($expected, $verbose);
    }

    /**
     * Get the status code
     *
     * @param Guzzle\Result $result The guzzle result instance
     * @return int
     */
    protected function getValueFromResult(Guzzle\Result $result)
    {
        return $result->getResponse()->getStatusCode();
    }

    /**
     * @return string
     */
    public function failureText($value)
    {
        return sprintf('has the status code %i', $this->getExpected());
    }
}
