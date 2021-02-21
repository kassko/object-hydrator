<?php

namespace Kassko\ObjectHydrator\Observer\Dto\DataSource;

final class BeforeFetchingData
{
    private $rawData;
    private ?string $dataSourceId = null;
    private string $containingClassName;
    private string $containingPropertyName;


    public function from(
        ?string $dataSourceId,
        string $containingClassName,
        string $containingPropertyName
    ) {
        return new self($dataSourceId, $containingClassName, $containingPropertyName);
    }

    public function initRawData($rawData) : self
    {
        $this->rawData = $rawData;

        return $this;
    }

    public function getRawData()
    {
        return $this->rawData;
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
        ?string $dataSourceId,
        string $containingClassName,
        string $containingPropertyName
    ) {
        $this->dataSourceId = $dataSourceId;
        $this->containingClassName = $containingClassName;
        $this->containingPropertyName = $containingPropertyName;
    }

    private function __clone() {}
}
