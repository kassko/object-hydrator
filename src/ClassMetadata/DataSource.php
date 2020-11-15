<?php

namespace Big\Hydrator\ClassMetadata;

use Big\StandardClassMetadata\{Method, Methods};

/**
 * @Annotation
 * @Target({"CLASS","ANNOTATION"})
 *
 * @author kko
 */
class DataSource extends Base
{
    //=== Annotations attributes (must be public) : begin ===//
    /**
     * @internal
     * @var string
     */
    public string $id;
    /**
     * @internal
     * @var \Big\StandardClassMetadata\Method
     */
    public Method $method;
    /**
     * @internal
     * @var bool
     */
    public bool $indexedByPropertiesKeys = false;
    /**
     * @internal
     */
    public ?string $fallBackDataSourceRef = null;
    /**
     * @internal
     * @var \Big\StandardClassMetadata\Methods
     */
    public ?Methods $afterMetadataLoading = null;
    /**
     * @internal
     * @var \Big\StandardClassMetadata\Methods
     */
    public ?Methods $beforeDataFetching = null;
    /**
     * @internal
     * @var \Big\StandardClassMetadata\Methods
     */
    public ?Methods $afterDataFetching = null;
    //=== Annotations attributes : end ===//

    /**
     * @var \Big\Hydrator\ClassMetadata\DataSource
     */
    private ?DataSource $fallBackDataSource = null;

    public function getId() : string
    {
        return $this->id;
    }

    public function getMethod() : Method
    {
        return $this->method;
    }

    public function isIndexedByPropertiesKeys() : bool
    {
        return $this->indexedByPropertiesKeys;
    }

    public function hasFallBackDataSource() : bool
    {
        return null !== $this->fallBackDataSource;
    }

    public function getFallBackDataSource() : ?DataSource
    {
        return $this->fallBackDataSource;
    }

    public function setFallbackDataSource(DataSource $dataSource) : self
    {
        $this->fallBackDataSource = $dataSource;
        return $this;
    }

    public function getAfterMetadataLoading() : ?Methods
    {
        return $this->afterMetadataLoading;
    }

    public function getBeforeDataFetching() : ?Methods
    {
        return $this->beforeDataFetching;
    }

    public function getAfterDataFetching() : ?Methods
    {
        return $this->afterDataFetching;
    }
}
