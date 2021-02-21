<?php

namespace Kassko\ObjectHydrator\Observer\Dto\Property;

use Kassko\ObjectHydrator\Model;

final class BeforeUsingMetadata
{
    private Model\Property\Leaf $propertyMetadata;
    private string $containingClassName;


    public function from(Model\Property\Leaf $propertyMetadata, string $containingClassName)
    {
        return new self($propertyMetadata, $containingClassName);
    }

    public function getPropertyMetadata() : Model\Property\Leaf
    {
        return $this->propertyMetadata;
    }

    public function getContainingClassName() : string
    {
        return $this->containingClassName;
    }

    private function __construct(Model\Property\Leaf $propertyMetadata, string $containingClassName)
    {
        $this->propertyMetadata = $propertyMetadata;
        $this->containingClassName = $containingClassName;
    }

    private function __clone() {}
}
