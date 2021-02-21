<?php

namespace Kassko\ObjectHydrator\Observer\Dto\DataSource;

use Kassko\ObjectHydrator\Model;

final class BeforeUsingMetadata
{
    private Model\DataSource $dataSourceMetadata;
    private string $containingClassName;
    private string $containingPropertyName;


    public function from(
        Model\DataSource $dataSourceMetadata,
        string $containingClassName,
        string $containingPropertyName
    ) {
        return new self($dataSourceMetadata, $containingClassName, $containingPropertyName);
    }

    public function getDataSourceMetadata() : Model\DataSource
    {
        return $this->dataSourceMetadata;
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
        Model\DataSource $dataSourceMetadata,
        string $containingClassName,
        string $containingPropertyName
    ) {
        $this->dataSourceMetadata = $dataSourceMetadata;
        $this->containingClassName = $containingClassName;
        $this->containingPropertyName = $containingPropertyName;
    }

    private function __clone() {}
}
