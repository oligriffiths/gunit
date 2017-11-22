<?php

namespace OliGriffiths\GUnit\PHPUnit\Constraint;

/**
 * Header constraint is used to validate a HTTP headers
 */
class BodyEquals extends Body
{
    /**
     * @param $value
     * @return mixed|string
     */
    public function failureText($value)
    {
        $export = $this->exporter->export($value);
        return sprintf('body matches "%s"', $export);
    }
}
