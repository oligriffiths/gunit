<?php

namespace OliGriffiths\GUnit\PHPUnit\Constraint;

use OliGriffiths\GUnit\Guzzle;

/**
 * Class AbstractConstraint
 */
abstract class AbstractConstraint extends \PHPUnit_Framework_Constraint
{
    /**
     * @var Guzzle\Result The guzzle result object
     */
    private $result;

    /**
     * @var mixed The expected value
     */
    private $expected;

    /**
     * @var bool In verbose mode, the full response is printed
     */
    private $verbose;

    /**
     * Constructor setups the constraint
     *
     * @param mixed $expected The expected to compare against
     * @param bool $verbose In verbose mode, the full response is printed
     */
    public function __construct($expected, $verbose = false)
    {
        parent::__construct();

        $this->expected = $expected;
        $this->verbose = $verbose;
    }

    /**
     * @return Guzzle\Result
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @return mixed
     */
    public function getExpected()
    {
        return $this->expected;
    }

    /**
     * @return bool
     */
    public function isVerbose()
    {
        return $this->verbose;
    }

    /**
     * Compares the returned value against the expected value
     *
     * @param mixed $value The value to compare
     * @return bool
     */
    protected function matches($value)
    {
        return $value == $this->expected;
    }

    /**
     * @inheritDoc
     */
    public function evaluate($other, $description = '', $returnResult = false)
    {
        if (!$other instanceof Guzzle\Result) {
            throw \PHPUnit_Util_InvalidArgumentHelper::factory(1, Guzzle\Result::class);
        }

        $this->result = $other;

        $value = $this->getValueFromResult($this->result);
        return parent::evaluate($value, $description, $returnResult);
    }

    /**
     * Retrieves the value to use to pass to the matches() method
     *
     * @param Guzzle\Result $result The guzzle result object
     * @return mixed
     */
    protected abstract function getValueFromResult(Guzzle\Result $result);

    /**
     * Returns the description of the failure
     *
     * The beginning of failure messages is "Failed asserting that" in most
     * cases. This method should return the second part of that sentence.
     *
     * @param mixed $other Evaluated value or object.
     *
     * @return string
     */
    protected function failureDescription($other)
    {
        return sprintf(
            'the response %s',
            $this->failureText($other)
        );
    }

    /**
     * @param $value
     * @return mixed
     */
    abstract protected function failureText($value);

    /**
     * @return string
     */
    public function toString()
    {
        return '';
    }
    
    /**
     * @param mixed $other
     * @return string
     */
    protected function additionalFailureDescription($other)
    {
        if ($this->verbose) {
            return PHP_EOL . $this->getResult()->toString();
        }

        return 'URI: ' . $this->getResult()->getRequest()->getUri();
    }
}
