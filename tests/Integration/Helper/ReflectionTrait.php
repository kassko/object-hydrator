<?php
namespace Kassko\ObjectHydratorIntegrationTest\Helper;

trait ReflectionTrait
{
    private function getPropertyValue(object $object, string $propertyName)
    {
        $reflectionClass = new \ReflectionClass($object);
        $reflectionProperty = $reflectionClass->getProperty($propertyName);
        $reflectionProperty->setAccessible(true);

        return $reflectionProperty->getValue($object);
    }
}
