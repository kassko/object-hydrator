<?php

namespace Big\Hydrator;

use Big\Hydrator\ClassMetadata\{DataSource, DataSources, Property};
use Big\StandardClassMetadata\Methods;

/**
 * @Annotation
 * @Target("PROPERTY")
 *
 * @author kko
 */
class ClassMetadata
{
    //=== Original metadata : begin ===//
    /**
     * @internal
     * @var bool
     */
    public bool $propertiesExcludedByDefault = false;
    /**
     * @internal
     * @var \Big\Hydrator\ClassMetadata\Methods
     */
    public ?Methods $beforeUsingLoadedMetadata = null;
    /**
     * @internal
     * @var \Big\Hydrator\ClassMetadata\Methods
     */
    public ?Methods $afterUsingLoadedMetadata = null;
    /**
     * @internal
     * @var \Big\Hydrator\ClassMetadata\Methods
     */
    public ?Methods $beforeHydration = null;
    /**
     * @internal
     * @var \Big\Hydrator\ClassMetadata\Methods
     */
    public ?Methods $afterHydration = null;

    private array $properties = [];
    private array $includedProperties = [];
    private array $excludedProperties = [];
    //=== Original metadata: end ===//

    private array $managedPropertiesVersions = [];
    private array $basicManagedPropertiesVersions = [];
    private array $loadableManagedPropertiesVersions = [];
    private DataSources $dataSources;
    private Conditionals $conditionals;
    private \ReflectionClass $reflectionClass;

    public function __construct(object $object)
    {
        $this->reflectionClass = new \ReflectionClass($object);
    }

    public function getReflectionClass() : \ReflectionClass
    {
        return $this->reflectionClass;
    }

    public function afterMetadataLoaded()
    {
        $this->compile();
        $this->compute();
    }

    public function getBasicManagedPropertiesVersions() : array
    {
        return $this->basicManagedPropertiesVersions;
    }

    public function getLoadableManagedPropertiesVersions() : array
    {
        return $this->loadableManagedPropertiesVersions;
    }

    public function getManagedPropertyVersions($propertyName) : array
    {
        return $this->managedPropertiesVersions[$propertyName] ?? null;
    }

    public function findDataSource(string $id) : ?DataSource
    {
        foreach ($this->dataSources->items as $key => $dataSource) {
            if ($id === $dataSource->id) {
                return $dataSource;
            }
        }

        return null;
    }

    public function findDataSourcesByTag(string $tag) : array
    {
        $dataSources = [];

        foreach ($this->dataSources->items as $key => $dataSource) {
            if ($tag === $dataSource->tag) {
                $dataSources[$dataSource->id] = $dataSource;
            }
        }

        return $dataSources;
    }

    public function findConditional(string $id) : ?DataSource
    {
        foreach ($this->conditionals->items as $key => $conditional) {
            if ($id === $conditional->id) {
                return $conditional;
            }
        }

        return null;
    }

    private function compile() : void
    {
        $this->compileManagedProperties();
        $this->compileManagedPropertiesDataSources();
        $this->compileManagedPropertiesConditionals();
    }

    private function compileManagedProperties() : void
    {
        if ($this->propertiesExcludedByDefault) {
             foreach ($this->reflectionClass->getProperties() as $reflectionProperty) {
                if (isset($this->includedProperties[$name = $reflectionProperty->getName()])) {//Property is explicitly included with given config and so managed.
                    $versions = [];
                    foreach ($this->includedProperties[$name] as $property) {
                        $versions[] = $property->compile($name);
                    }
                    $this->managedPropertiesVersions[$name] = new PropertyVersions($name, $versions);
                }

                //Properties which are not explicitly included are not managed.
            }
        } else {
            foreach ($this->reflectionClass->getProperties() as $reflectionProperty) {
                if (isset($this->includedProperties[$name = $reflectionProperty->getName()])) {//Property is explicitly included with given config and so managed.
                    $versions = [];
                    foreach ($this->includedProperties[$name] as $property) {
                       $versions[] = $property->compile($reflectionProperty->getName());
                    }
                    $this->managedPropertiesVersions[$name] = new PropertyVersions($name, $versions);
                } elseif (! isset($this->excludedProperties[$name = $reflectionProperty->getName()])) {//Property is implicitly included with a default config and so managed.
                    $this->managedPropertiesVersions[$name] = new PropertyVersions(
                        $name,
                        [(new ClassMetadata\Property)->compile($reflectionProperty->getName())]
                    );
                }
            }
        }
    }

    private function compileManagedPropertiesDataSources() : void
    {
        foreach ($this->managedPropertiesVersions as $propertyVersions) {
            foreach ($propertyVersions->getVersions() as $property) {
                if ($property->hasDataSourceRef() && null !== ($dataSource = $this->findDataSource($property->getDataSourceRef()))) {

                    //Move this in class "Method"
                    $reflClass = new \ReflectionClass($dataSource->getMethod()->getClass());
                    $dataSource->getMethod()->setReflector($reflClass->getMethod($dataSource->getMethod()->getName()));

                    $property->setDataSource($dataSource);
                }
            }
        }
    }

    private function compileManagedPropertiesConditionals() : void
    {
        foreach ($this->managedPropertiesVersions as $propertyVersions) {
            foreach ($propertyVersions->getVersions() as $property) {
                if ($property->hasConditionalRef() && null !== ($conditional = $this->findConditional($property->getConditionalRef()))) {

                    //Move this in class "Method"
                    if ($conditional instanceof ConditionalMethod) {
                        $reflClass = new \ReflectionClass($conditional->getMethod()->getClass());
                        $conditional->getMethod()->setReflector($reflClass->getMethod($conditional->getMethod()->getName()));
                    }

                    $property->setConditional($conditional);
                }
            }
        }
    }

    private function compute() : void
    {
        $this->computeBasicManagedPropertiesVersions();
        $this->computeLoadableManagedPropertiesVersions();
    }

    private function computeBasicManagedPropertiesVersions() : void
    {
        foreach ($this->managedPropertiesVersions as $propertyName => &$propertyVersions) {
            $this->basicManagedPropertiesVersions[$propertyName] = new PropertyVersions(
                $propertyName,
                array_filter($propertyVersions->getVersions(), fn($property) => ! $property->hasDataSource())
            );
        }
    }

    private function computeLoadableManagedPropertiesVersions() : void
    {
        foreach ($this->managedPropertiesVersions as $propertyName => &$propertyVersions) {
            $this->loadableManagedPropertiesVersions[$propertyName] = new PropertyVersions(
                $propertyName,
                array_filter($propertyVersions->getVersions(), fn($property) => ! $property->hasDataSource())
            );
        }
    }

    public function setPropertiesExcludedByDefault(bool $value = true) : self
    {
        $this->propertiesExcludedByDefault = $value;
        return $this;
    }

    public function addIncludedProperty(string $propertyName, Property $property) : self
    {
        if (! isset($this->includedProperties[$propertyName])) {
            $this->includedProperties[$propertyName] = [];
        }
        $this->includedProperties[$propertyName][] = $property;

        return $this;
    }

    public function addExcludedProperty(string $propertyName, Property $property) : self
    {
        $this->excludedProperties[$propertyName] = $property;
        return $this;
    }

    public function setDataSources(DataSources $dataSources) : self
    {
        $this->dataSources = $dataSources;
        return $this;
    }

    public function setConditionals(Conditionals $conditionals) : self
    {
        $this->conditionals = $conditionals;
        return $this;
    }

    public function getBeforeUsingLoadedMetadata() : ?Methods
    {
        return $this->beforeUsingLoadedMetadata;
    }

    public function getAfterUsingLoadedMetadata() : ?Methods
    {
        return $this->afterUsingLoadedMetadata;
    }

    public function getBeforeHydration() : ?Methods
    {
        return $this->beforeHydration;
    }

    public function getAfterHydration() : ?Methods
    {
        return $this->afterHydration;
    }
}
