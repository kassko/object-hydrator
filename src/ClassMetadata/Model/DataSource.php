<?php

namespace Big\Hydrator\ClassMetadata\Model;

use Doctrine\Common\Collections\ArrayCollection;

use function array_combine;

/**
 * @author kko
 */
final class DataSource
{
    public const LOADING_MODE_LAZY = 'lazy';
    public const LOADING_MODE_EAGER = 'eager';
    public const LOADING_SCOPE_DATA_SOURCE = 'data_source';
    public const LOADING_SCOPE_PROPERTY = 'property';
    public const LOADING_SCOPE_DATA_SOURCE_ONLY_KEYS = 'data_source_only_keys';
    public const LOADING_SCOPE_DATA_SOURCE_EXCEPT_KEYS = 'data_source_except_keys';

    private ?string $id;
    private Method $method;
    private string $loadingMode = self::LOADING_MODE_LAZY;
    private string $loadingScope = self::LOADING_SCOPE_DATA_SOURCE;
    private array $loadingScopeKeys = [];
    private bool $indexedByPropertiesKeys = false;
    private ?DataSource $fallBackDataSource = null;
    private ?string $fallBackDataSourceRef = null;
    private ?Callbacks $callbacksUsingMetadata = null;
    private ?Callbacks $callbacksFetchingData = null;
    private ?Callbacks $callbacksHydration = null;
    private ?Callbacks $callbacksAssigningHydratedValue = null;


    public function __construct(?string $id = null)
    {
        $this->id = $id;

        $this->callbacksUsingMetadata = new Callbacks;
        $this->callbacksFetchingData = new Callbacks;
        $this->callbacksHydration = new Callbacks;
        $this->callbacksAssigningHydratedValue = new Callbacks;
    }

    public function getId() : ?string
    {
        return $this->id;
    }

    public function getMethod() : Method
    {
        return $this->method;
    }

    public function setMethod(Method $method) : self
    {
        $this->method = $method;

        return $this;
    }

    public function mustBeLazyLoaded() : bool
    {
        return self::LOADING_MODE_LAZY === $this->loadingMode;
    }

    public function mustBeEagerLoaded() : bool
    {
        return self::LOADING_MODE_EAGER === $this->loadingMode;
    }

    public function setLoadingMode(string $loadingMode) : self
    {
        $this->loadingMode = $loadingMode;

        return $this;
    }

    public function isIndexedByPropertiesKeys() : bool
    {
        return $this->indexedByPropertiesKeys;
    }

    public function setIndexedByPropertiesKeys(bool $indexedByPropertiesKeys) : self
    {
        $this->indexedByPropertiesKeys = $indexedByPropertiesKeys;

        return $this;
    }

    public function getLoadingScope() : string
    {
        return $this->loadingScope;
    }

    public function getLoadingScopeKeys() : array
    {
        return $this->loadingScopeKeys;
    }

    public function setLoadingScope(string $scope, array $scopeKeys = [])
    {
        $this->loadingScope = $scope;
        $this->loadingScopeKeys = array_combine($scopeKeys, $scopeKeys);
    }

    public function hasFallbackDataSource() : bool
    {
        return null !== $this->fallbackDataSource;
    }

    public function getFallBackDataSource() : ?DataSource
    {
        return $this->fallbackDataSource;
    }

    public function setFallbackDataSource(DataSource $dataSource) : self
    {
        $this->fallBackDataSource = $dataSource;

        return $this;
    }

    public function getCallbacksUsingMetadata() : ?Callbacks
    {
        return $this->callbacksUsingMetadata;
    }

    public function setCallbacksUsingMetadata(Callbacks $callbacksUsingMetadata) : self
    {
        $this->callbacksUsingMetadata = $callbacksUsingMetadata;

        return $this;
    }

    public function getCallbacksFetchingData() : ?Callbacks
    {
        return $this->callbacksFetchingData;
    }

    public function setCallbacksFetchingData(Callbacks $callbacksFetchingData) : self
    {
        $this->callbacksFetchingData = $callbacksFetchingData;

        return $this;
    }

    public function getCallbacksHydration() : ?Callbacks
    {
        return $this->callbacksHydration;
    }

    public function setCallbacksHydration(Callbacks $callbacksHydration) : self
    {
        $this->callbacksHydration = $callbacksHydration;

        return $this;
    }

    public function getCallbacksAssigningHydratedValue() : ?Callbacks
    {
        return $this->callbacksAssigningHydratedValue;
    }

    public function setCallbacksAssigningHydratedValue(Callbacks $callbacksAssigningHydratedValue) : self
    {
        $this->callbacksAssigningHydratedValue = $callbacksAssigningHydratedValue;

        return $this;
    }
}
