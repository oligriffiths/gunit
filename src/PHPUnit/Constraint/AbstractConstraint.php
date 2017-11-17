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
    protected $result;

    /**
     * @var mixed The expected value
     */
    protected $expected;

    /**
     * @var string The name of this constraint used for error strings @see AbstractConstraint::failureDescription()
     */
    protected $name;

    /**
     * @var bool In verbose mode, the full response is printed
     */
    protected $verbose;

    /**
     * Constructor setups the constraint
     *
     * @param mixed $expected The expected to compare against
     * @param string $name An optional name to use in the error output.
     * @param bool $verbose In verbose mode, the full response is printed
     *                     Used in the format "Failed asserting that the response $name of $expected matches the expected $name of $value."
     */
    public function __construct($expected, $verbose = false, $name = '')
    {
        parent::__construct();

        $this->expected = $expected;
        $this->name = $name;
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
     * @return string
     */
    public function getName()
    {
        return $this->name;
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
     * @param mixed $other
     * @return string
     */
    protected function failureDescription($other)
    {
        $value = $this->exporter->export($other);
        $prefix = ($this->name ? 'the response ' . $this->name . ' of ' : '');
        $uri = $this->result->getRequest()->getUri();

        $description = $prefix . $value . ' ' . $this->toString();

        if ($this->verbose) {
            $description .= PHP_EOL . $this->result->toString();
        } else {
            $description .= ' - URI: ' . $uri;
        }

        return $description;
    }

    /**
     * @return string
     */
    public function toString()
    {
        $value = $this->exporter->export($this->expected);
        return 'matches the expected ' . ($this->name ? $this->name . ' of ' : '') . $value;
    }
}
