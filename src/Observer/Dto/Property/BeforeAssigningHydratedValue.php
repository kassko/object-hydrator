<?php

namespace Kassko\ObjectHydrator\Observer\Dto\Property;

final class BeforeAssigningHydratedValue
{
    private $modelValueToAssign;
    private string $propertyToBeAssignedName;
    private string $containingClassName;


    public function from($modelValueToAssign, string $propertyToBeAssignedName, string $containingClassName)
    {
        return new self($modelValueToAssign, $propertyToBeAssignedName, $containingClassName);
    }

    public function getModelValueToAssign()
    {
        return $this->modelValueToAssign;
    }

    public function updateModelValueToAssign($modelValueToAssign) : self
    {
        $this->modelValueToAssign = $modelValueToAssign;

        return $this;
    }

    public function getPropertyToBeAssignedName() : string
    {
        return $this->propertyToBeAssignedName;
    }

    public function getContainingClassName() : string
    {
        return $this->containingClassName;
    }

    private function __construct($modelValueToAssign, string $propertyToBeAssignedName, string $containingClassName)
    {
        $this->modelValueToAssign = $modelValueToAssign;
        $this->propertyToBeAssignedName = $propertyToBeAssignedName;
        $this->containingClassName = $containingClassName;
    }

    private function __clone() {}
}
