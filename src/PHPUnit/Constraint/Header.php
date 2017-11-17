<?php

namespace OliGriffiths\GUnit\PHPUnit\Constraint;

use OliGriffiths\GUnit\Guzzle;

/**
 * Header constraint is used to validate a HTTP headers
 */
class Header extends AbstractConstraint
{
    /**
     * @var string
     */
    protected $header;

    /**
     * Header constructor, sets the name
     *
     * @param mixed $expected The expected status code to compare against
     * @param mixed $header The header name to compare
     * @param bool $verbose True if enabling verbose mode
     */
    public function __construct($expected, $header, $verbose = false)
    {
        parent::__construct($expected, $verbose,'header');

        $this->header = $header;
    }

    protected function matches(array $values)
    {
        sort($values);
        $expected = sort($this->getExpected());

        
    }

    /**
     * Get the status code
     *
     * @param Guzzle\Result $result The guzzle result instance
     * @return array
     */
    protected function getValueFromResult(Guzzle\Result $result)
    {
        return $result->getResponse()->getHeader($this->header);
    }
}
