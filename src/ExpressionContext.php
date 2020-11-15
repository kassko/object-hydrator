<?php

namespace Big\Hydrator;

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
     * Contains all the context variables.
     * @var array
     */
    private array $variables = [];

    /**
     * Return all the context variables.
     * @return array
     */
    public function getVariables() : array
    {
    	return $this->variables;
    }

    /**
     * Add a variable in the context.
     *
     * @param string $key A key to store the variable.
     * @param mixed $value The variable to store.
     */
    public function addVariable($key, $value) : void
    {
        if (is_null($key) || ! is_string($key)) {
            throw new \RuntimeException(sprintf('The key where to save your variable in the expression context is invalid. Got "%s".', $key));
        }

        $this->variables[$key] = $value;
    }

    public function flush() : void
    {
        $this->variables = [];
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($key) : bool
    {
        return array_key_exists($key, $this->variables);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($key)
    {
        if ($this->offsetExists($key)) {
            return $this->variables[$key];
        }

        throw new \RuntimeException(sprintf('No variable registered on key "%s" in expression context.', $key));
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value) : void
    {
        $this->addVariable($offset, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($key) : void
    {
        unset($this->variables[$key]);
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator() : \Traversable
    {
        return new ArrayIterator($this->variables);
    }
}
