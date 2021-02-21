<?php

namespace Kassko\ObjectHydrator\Observer\Dto\DataSource;

final class AfterFetchingData
{
    private $rawData;
    private ?string $dataSourceId = null;
    private string $containingClassName;
    private string $containingPropertyName;


    public function from(
        $rawData,
        ?string $dataSourceId,
        string $containingClassName,
        string $containingPropertyName
    ) {
        return new self($rawData, $dataSourceId, $containingClassName, $containingPropertyName);
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

    public function getDataSourceId() : ?string
    {
        return $this->dataSourceId;
    }

    public function getContainingClassName() : string
    {
        return $this->containingClassName;
    }

    public function getContainingPropertyName() : string
    {
        return $this->containingPropertyName;
    }

    private function __construct(
        $rawData,
        ?string $dataSourceId,
        string $containingClassName,
        string $containingPropertyName
    ) {
        $this->rawData = $rawData;
        $this->dataSourceId = $dataSourceId;
        $this->containingClassName = $containingClassName;
        $this->containingPropertyName = $containingPropertyName;
    }

    private function __clone() {}
}
