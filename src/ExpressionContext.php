<?php

namespace Kassko\ObjectHydrator;

use ArrayAccess;
use ArrayIterator;
use IteratorAggregate;

use function array_key_exists;
use function is_null;
use function is_string;
use function sprintf;

/**
* ExpressionContext
*
* @author kko
*/
class ExpressionContext implements ArrayAccess, IteratorAggregate
{
    /**
     * Contains all the context values.
     * @var array
     */
    private array $values = [];

    /**
     * Return all the context values.
     * @return array
     */
    public function getValues() : array
    {
    	return $this->values;
    }

    /**
     * Add a value in the context.
     *
     * @param string $key A key to store the value.
     * @param mixed $value The value to store.
     */
    public function addValue($key, $value) : void
    {
        if (is_null($key) || ! is_string($key)) {
            throw new \RuntimeException(sprintf('The key where to save your value in the expression context is invalid. Got "%s".', $key));
        }

        $this->values[$key] = $value;
    }

    public function flush() : void
    {
        $this->values = [];
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($key) : bool
    {
        return array_key_exists($key, $this->values);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($key)
    {
        if ($this->offsetExists($key)) {
            return $this->values[$key];
        }

        throw new \RuntimeException(sprintf('No value registered on key "%s" in expression context.', $key));
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value) : void
    {
        $this->addValue($offset, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($key) : void
    {
        unset($this->values[$key]);
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator() : \Traversable
    {
        return new ArrayIterator($this->values);
    }
}
