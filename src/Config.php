<?php

namespace Big\Hydrator;

use function array_key_exists;
use function is_array;
use function sprintf;

final class Config implements \ArrayAccess, \IteratorAggregate
{
    private const PATH_DELIMITER = '.';

    /**
     * [
     *      'mapping' => [
     *          'global' => [
     *              'prefer_explicit_properties_config',
     *              'prefer_doctrine_annotations',
     *          ],
     *          'by-class' => [
     *          ]
     *      ]
     * ]
     */
    private array $values;

    public function __construct(array $values = [])
    {
        $this->values = $values;
    }

    public function getPartition(string $path) : self
    {
        $valuesPartition = $this->getValueByPath($path);

        if (! is_array($valuesPartition)) {
            throw new \LogicException(sprintf(
                'Cannot get a partition of the config from the path "%1$s". ' .
                'A partition consist in an array of keys or other pathes. ' .
                'But the given path "%1$s" contains the scalar value "%2$s".',
                $path,
                $valuesPartition
            ));
        }

        return new self($valuesPartition);
    }

    public function getValues()
    {
        return $this->values;
    }

    public function getValue(string $key)
    {
        if (! isset($this->values[$key])) {
            throw new \LogicException(sprintf(
                'Cannot get a value for the key "%s" from class metadata config.',
                $key
            ));
        }

        return $this->values[$key];
    }

    private function getValueByPath(string $path)
    {
        $pathParts = explode(self::PATH_DELIMITER, $path);

        $previousValuesPartition = [];
        $valuesPartition = $this->values;
        $pathCursor = '';
        foreach ($pathParts as $pathPart) {
            $pathCursor .= $pathCursor ? self::PATH_DELIMITER . $pathPart : $pathPart;

            if (!isset($valuesPartition[$pathPart])) {
                $message = sprintf(
                    'Cannot get a value for the path "%s" from class metadata config.',
                    $message,
                    $path
                );

                $availableKeys = implode(',', array_keys($previousValuesPartition));
                if (count($availableKeys)) {
                    $message .= sprintf(' Available keys in path "%s" are "[%s]".', $pathCursor, $availableKeys);
                }

                throw new \LogicException($message);
            }

            $previousValuesPartition = $valuesPartition;
            $valuesPartition = $valuesPartition[$pathPart];
        }

        return $valuesPartition;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($key) : bool
    {
        return isset($this->values[$key]);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($key)
    {
        if (isset($this->values[$key])) {
            return $this->values[$key];
        }

        throw new \RuntimeException(sprintf('Cannot find key "%s" in config.', $key));
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($key, $value) : void
    {
        $this->values[$key] = $value;
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
        return new \ArrayIterator($this->values);
    }
}
