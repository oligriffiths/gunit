<?php

namespace OliGriffiths\GUnit\PHPUnit\Constraint;

/**
 * Header constraint is used to validate a HTTP headers
 */
class HeaderEquals extends Header
{
    /**
     * Header constructor, sets the name
     *
     * @param string $header The header name to check
     * @param mixed $expected The expected value for the header
     * @param bool $verbose True if enabling verbose mode
     */
    public function __construct($header, $expected, $verbose = false)
    {
        if (!is_string($expected) && !is_array($expected)) {
            throw \PHPUnit_Util_InvalidArgumentHelper::factory(2, 'string or array');
        }
        
        parent::__construct($header, $expected, $verbose);
    }

    /**
     * @param array $values
     */
    protected function matches($values)
    {
        $expected = $this->getExpected();
        
        if (empty($expected)) {
            return empty($values);
        }
        
        if (is_string($expected)) {
            return in_array($expected, $values, true);
        }
        
        foreach ($expected as $expect) {
            if (!in_array($expect, $values, true)) {
                return false;
            }
        }
    }

    /**
     * @return string
     */
    public function failureText($value)
    {
        $value = implode(',', $value);
        $expected = implode(',', (array) $this->getExpected());
        
        return sprintf(
            'header "%s" has expected value "%s", received "%s"',
            $this->getHeader(),
            $expected,
            $value
        );
    }
}
