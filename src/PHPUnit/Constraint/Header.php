<?php

namespace OliGriffiths\GUnit\PHPUnit\Constraint;

use OliGriffiths\GUnit\Guzzle;

/**
 * Header constraint is used to validate a HTTP headers
 */
abstract class Header extends AbstractConstraint
{
    /**
     * @var string
     */
    private $header;

    /**
     * Header constructor, sets the name
     *
     * @param mixed $header The header name to check
     * @param mixed $expected Optional expected value for the header
     * @param bool $verbose True if enabling verbose mode
     */
    public function __construct($header, $expected = null, $verbose = false)
    {
        parent::__construct($expected, $verbose);

        $this->header = $header;
    }

    /**
     * @return string
     */
    public function getHeader()
    {
        return $this->header;
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
