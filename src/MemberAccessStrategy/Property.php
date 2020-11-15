<?php

namespace Big\Hydrator\MemberAccessStrategy;

use Big\Hydrator\ClassMetadata;
use Big\Hydrator\MemberAccessStrategy\Exception\NotFoundMemberException;

/**
* Access logic by property to object members to hydrate.
*
* @author kko
*/
class Property implements \Big\Hydrator\MemberAccessStrategyInterface
{
    private $reflectionClass;

    public function prepare(object $object, ClassMetadata $classMetadata) : void
    {
        $this->reflectionClass = $classMetadata->getReflectionClass();
    }

    public function getValue(object $object, string $fieldName)
    {
        return $this->doGetValue($object, $fieldName);
    }

    public function setValue($value, object $object, string $fieldName) : void
    {
        if (! isset($fieldName)) {
            return;
        }

        $this->doSetValue($fieldName, $object, $value);
    }

    private function doGetValue(object $object, string $fieldName)
    {
        $reflProperty = $this->getAccessibleProperty($fieldName);
        if (false === $reflProperty) {
            throw new NotFoundMemberException(sprintf('Not found member "%s::%s"', get_class($object), $fieldName));
        }

        return $reflProperty->getValue($object);
    }

    private function doSetValue(string $fieldName, object $object, $value) : void
    {
        $reflProperty = $this->getAccessibleProperty($fieldName);
        if (false === $reflProperty) {
            return;
        }

        $reflProperty->setValue($object, $value);
    }

    private function getAccessibleProperty(string $fieldName) : \ReflectionProperty
    {
        if (! $this->reflectionClass->hasProperty($fieldName)) {
            return false;
        }

        $reflProperty = $this->reflectionClass->getProperty($fieldName);
        if (! $reflProperty->isPublic()) {
            $reflProperty->setAccessible(true);
        }

        return $reflProperty;
    }
}
