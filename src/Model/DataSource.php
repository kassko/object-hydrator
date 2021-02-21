<?php

namespace Kassko\ObjectHydrator\Model;

use Kassko\ObjectHydrator\Model\Enum;
use Doctrine\Common\Collections\ArrayCollection;

use function array_combine;

/**
 * @author kko
 */
final class DataSource
{
    private ?string $id;
    private Method $method;
    private string $loadingMode = Enum\DataSourceLoadingMode::LAZY;
    private string $loadingScope = Enum\DataSourceLoadingScope::DATA_SOURCE;
    private array $loadingScopeKeys = [];
    private bool $indexedByPropertiesKeys = true;
    private ?DataSource $fallbackDataSource = null;
    private ?string $fallbackDataSourceRef = null;
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
        return Enum\DataSourceLoadingMode::LAZY === $this->loadingMode;
    }

    public function mustBeEagerLoaded() : bool
    {
        return Enum\DataSourceLoadingMode::EAGER === $this->loadingMode;
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
