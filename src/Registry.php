<?php

namespace Kassko\ObjectHydrator;

/**
 * Registry
 *
 * @author kko
 */
final class Registry implements \ArrayAccess, \IteratorAggregate
{
    public const KEY_PROPERTY_LOADER = 'property_loader';
    public const KEY_LOGGER = 'logger';

    /**
     * Contains data
     * @var array
     */
    private array $registry = [];

    public static function getInstance() : Registry
    {
        static $instance;

        if (null === $instance) {
            $instance = new self;
        }

        return $instance;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($key)
    {
        return array_key_exists($key, $this->registry);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($key)
    {
        if ($this->offsetExists($key)) {
            return $this->registry[$key];
        }

        throw new \RuntimeException(sprintf('No data registered on key "%s" in the registry.', $key));
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            throw new \RuntimeException('You should specify an index where to save in the registry. Got value "null".', $offset);
        }

        /*
        if ($this->offsetExists($offset)) {
            throw new \RuntimeException(sprintf('The key "%s" cannot be overriden in the registry.', $offset));
        }
        */

        $this->registry[$offset] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($key)
    {
        unset($this->registry[$key]);
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator() {
        return new \ArrayIterator($this->registry);
    }

    public function clear()
    {
        $this->registry = [];
    }

    private function __construct() {}

    private function __clone() {}
}
