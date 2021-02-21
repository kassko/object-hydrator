<?php

namespace Kassko\ObjectHydrator\Observer\Dto\Property;

final class BeforeHydration
{
    private $rawData;
    private string $propertyName;
    private string $containingClassName;


    public function from($rawData, string $propertyName, string $containingClassName)
    {
        return new self($rawData, $propertyName, $containingClassName);
    }

    public function getRawData()
    {
        return $this->rawData;
    }

    public function updateRawData($rawData) : self
    {
        $this->rawData = $rawData;

        return $this;
    }

    public function getPropertyName() : string
    {
        return $this->propertyName;
    }

    public function getContainingClassName() : string
    {
        return $this->containingClassName;
    }

    private function __construct($rawData, string $propertyName, string $containingClassName)
    {
        $this->rawData = $rawData;
        $this->propertyName = $propertyName;
        $this->containingClassName = $containingClassName;
    }

    private function __clone() {}
}
