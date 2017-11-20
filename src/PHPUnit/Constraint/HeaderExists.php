<?php

namespace OliGriffiths\GUnit\PHPUnit\Constraint;

/**
 * Header constraint is used to validate a HTTP headers
 */
class HeaderExists extends Header
{
    /**
     * Header constructor, sets the name
     *
     * @param mixed $header The header name to check
     * @param bool $verbose True if enabling verbose mode
     */
    public function __construct($header, $verbose = false)
    {
        parent::__construct($header, null, $verbose);
    }

    /**
     * @param array $values
     */
    protected function matches($values)
    {
        return !empty($values);
    }

    /**
     * @return string
     */
    public function failureText($value)
    {
        return sprintf('contains the header "%s"', $this->getHeader());
    }
}
