<?php

namespace Kassko\ObjectHydrator\MemberAccessStrategy;

use Kassko\ObjectHydrator\ClassMetadata;
use Kassko\ObjectHydrator\Model;
use Kassko\ObjectHydrator\MemberAccessStrategy\Exception\NotFoundMemberException;

/**
* Access logic by property to object members to set with hydrated/model value.
*
* @author kko
*/
class Property implements \Kassko\ObjectHydrator\MemberAccessStrategyInterface
{
    private $object;
    private $reflectionClass;

    public function __construct(object $object, Model\Class_ $classMetadata)
    {
        $this->object = $object;
        $this->reflectionClass = $classMetadata->getReflectionClass();
    }

    public function getValue(Model\Property\Leaf $property)
    {
        return $this->doGetValue($property->getName());
    }

    public function setValue($value, Model\Property\Leaf $property) : void
    {
        $propertyName = $property->getName();
        if (! isset($propertyName)) {
            return;
        }

        $this->doSetValue($propertyName, $value);
    }

    private function doGetValue(string $propertyName)
    {
        $accessor = \Closure::bind(function ($object, $propertyName) {
            return $object->$propertyName;
        }, null, get_class($this->object));

        return $accessor($this->object, $propertyName);

        /*$reflProperty = $this->getAccessibleProperty($propertyName);
        if (false === $reflProperty) {
            throw new NotFoundMemberException(sprintf('Not found member "%s::%s"', get_class($this->object), $propertyName));
        }

        return $reflProperty->getValue($this->object);*/
    }

    private function doSetValue(string $propertyName, $value) : void
    {
        $accessor = \Closure::bind(function ($object, $propertyName, $value) {
            $object->$propertyName = $value;
        }, null, get_class($this->object));

        $accessor($this->object, $propertyName, $value);

        /*
        $reflProperty = $this->getAccessibleProperty($propertyName);
        if (false === $reflProperty) {
            return;
        }

        //$reflProperty->setValue($this->object, $value);
        */
    }

    private function getAccessibleProperty(string $propertyName) : \ReflectionProperty
    {
        if (! $this->reflectionClass->hasProperty($propertyName)) {
            return false;
        }

        $reflProperty = $this->reflectionClass->getProperty($propertyName);
        if (! $reflProperty->isPublic()) {
            $reflProperty->setAccessible(true);
        }

        return $reflProperty;
    }
}
