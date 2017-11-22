<?php

namespace OliGriffiths\GUnit\PHPUnit\Constraint;

use Psr\Http\Message;
use SebastianBergmann;

/**
 * BodyKeyEquals constraint is used to validate a HTTP response body equals an expected value
 */
class BodyKeyEquals extends BodyKey
{
    /**
     * Check if the body contains the key and it matches the expected value
     *
     * @param Message\ResponseInterface $response The response object
     * @return mixed
     */
    protected function matches($response)
    {
        if (!$this->hasBodyKey($response, $this->getKey())) {
            return false;
        }

        $value = $this->getBodyKey($response, $this->getKey());

        if (!is_scalar($value)) {
            return $this->compareEquals($value, $this->getExpected());
        }

        $constraint = new \PHPUnit_Framework_Constraint_IsIdentical(
            $this->getExpected()
        );

        return $constraint->evaluate($value, null, true);
    }

    /**
     * Compares two values are equal using the comparator
     *
     * @param mixed $value The source value
     * @param mixed $expected The expected value
     * @return bool
     */
    private function compareEquals($value, $expected)
    {
        $comparatorFactory = SebastianBergmann\Comparator\Factory::getInstance();

        try {
            $comparator = $comparatorFactory->getComparatorFor(
                $expected,
                $value
            );

            $comparator->assertEquals(
                $expected,
                $value
            );
        } catch (SebastianBergmann\Comparator\ComparisonFailure $f) {
            return false;
        }

        return true;
    }
}
