<?php

namespace Kassko\ObjectHydrator\Observer\Dto\Property;

final class AfterAssigningHydratedValue
{
    private $modelValueAssigningProperty;
    private string $propertyAssignedName;
    private object $containingObject;


    public function from($modelValueAssigningProperty, string $propertyAssignedName, object $containingObject)
    {
        return new self($modelValueAssigningProperty, $propertyAssignedName, $containingObject);
    }

    public function getModelValueAssigningProperty()
    {
        return $this->modelValueAssigningProperty;
    }

    public function getPropertyToBeAssignedName() : string
    {
        return $this->propertyAssignedName;
    }

    public function getContainingObject() : object
    {
        return $this->containingObject;
    }

    private function __construct($modelValueAssigningProperty, string $propertyAssignedName, object $containingObject)
    {
        $this->modelValueAssigningProperty = $modelValueAssigningProperty;
        $this->propertyAssignedName = $propertyAssignedName;
        $this->containingObject = $containingObject;
    }

    private function __clone() {}
}
