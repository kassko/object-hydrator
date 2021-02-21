<?php

namespace Kassko\ObjectHydrator\Observer\Dto\Property;

final class AfterHydration
{
    private $model;
    private $rawData;
    private string $propertyName;
    private string $containingClassName;


    public function from($model, $rawData, string $propertyName, string $containingClassName)
    {
        return new self($model, $rawData, $propertyName, $containingClassName);
    }

    public function getModel()
    {
        return $this->model;
    }

    public function getRawData()
    {
        return $this->rawData;
    }

    public function getPropertyName() : string
    {
        return $this->propertyName;
    }

    public function getContainingClassName() : string
    {
        return $this->containingClassName;
    }

    private function __construct($model, $rawData, string $propertyName, string $containingClassName)
    {
        $this->model = $model;
        $this->rawData = $rawData;
        $this->propertyName = $propertyName;
        $this->containingClassName = $containingClassName;
    }

    private function __clone() {}
}
