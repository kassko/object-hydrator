<?php

namespace Big\Hydrator;

use Big\Hydrator\ClassMetadata;
use Big\StandardClassMetadata as StdClassMetadata;

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
    public ?StdClassMetadata\Methods $beforeUsingLoadedMetadata = null;
    /**
     * @internal
     * @var \Big\Hydrator\ClassMetadata\Methods
     */
    public ?StdClassMetadata\Methods $afterUsingLoadedMetadata = null;
    /**
     * @internal
     * @var \Big\Hydrator\ClassMetadata\Methods
     */
    public ?StdClassMetadata\Methods $beforeHydration = null;
    /**
     * @internal
     * @var \Big\Hydrator\ClassMetadata\Methods
     */
    public ?StdClassMetadata\Methods $afterHydration = null;

    private array $explicitlyIncludedProperties = [];
    private array $explicitlyExcludedProperties = [];
    //=== Original metadata: end ===//

    private array $candidatesProperties = [];
    private array $basicCandidatesProperties = [];
    private array $loadableCandidatesProperties = [];
    private ClassMetadata\DataSources $dataSources;
    private ClassMetadata\Conditionals $conditionals;
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

    public function getBasicCandidatesProperties() : array
    {
        return $this->basicCandidatesProperties;
    }

    public function getLoadableCandidatesProperties() : array
    {
        return $this->loadableCandidatesProperties;
    }

    public function getCandidateProperties($propertyName) : ClassMetadata\CandidateProperties
    {
        return $this->candidatesProperties[$propertyName];
    }

    public function findDataSource(string $id) : ClassMetadata\DataSource
    {
        foreach ($this->dataSources->items as $key => $dataSource) {
            if ($id === $dataSource->id) {
                return $dataSource;
            }
        }

        throw new \LogicException(sprintf('Cannot find datasource "%s".', $id));
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

    public function findConditional(string $id) : object
    {
        foreach ($this->conditionals->items as $key => $conditional) {
            if ($id === $conditional->id) {
                return $conditional;
            }
        }

        throw new \LogicException(sprintf('Cannot find conditional "%s".', $id));
    }

    private function compile() : void
    {
        $this->compileProperties();
        $this->compilePropertiesDataSources();
        $this->compilePropertiesConditionals();
    }

    private function compileProperties() : void
    {
        if ($this->propertiesExcludedByDefault) {
            foreach ($this->reflectionClass->getProperties() as $reflectionProperty) {
                if (isset($this->explicitlyIncludedProperties[$name = $reflectionProperty->getName()])) {//Property is explicitly included with given config and so managed.
                    foreach ($this->explicitlyIncludedProperties[$name] as $candidateProperty) {
                        $candidateProperty->compile($reflectionProperty);
                    }
                    $this->candidatesProperties[$name] = $this->explicitlyIncludedProperties[$name];
                }

                //Properties which are not explicitly included are not managed.
            }
        } else {
            foreach ($this->reflectionClass->getProperties() as $reflectionProperty) {
                if (isset($this->explicitlyIncludedProperties[$name = $reflectionProperty->getName()])) {//Property is explicitly included with given config and so managed.
                    foreach ($this->explicitlyIncludedProperties[$name] as $candidateProperty) {
                        $candidateProperty->compile($reflectionProperty);
                    }
                    $this->candidatesProperties[$name] = $this->explicitlyIncludedProperties[$name];
                } elseif (! isset($this->explicitlyExcludedProperties[$name = $reflectionProperty->getName()])) {//Property is implicitly included with a default config and so managed.
                    $candidateProperties = new ClassMetadata\CandidateProperties;
                    $candidateProperties->items[] = (new ClassMetadata\Property)->compile($reflectionProperty);
                }
            }
        }
    }

    private function compilePropertiesDataSources() : void
    {
        foreach ($this->candidatesProperties as $candidateProperties) {
            foreach ($candidateProperties->items as $candidateProperty) {
                if ($candidateProperty->hasDataSourceRef() && null !== ($dataSource = $this->findDataSource($candidateProperty->getDataSourceRef()))) {

                    //Move this in class "Method"
                    $reflClass = new \ReflectionClass($dataSource->getMethod()->getClass());
                    $dataSource->getMethod()->setReflector($reflClass->getMethod($dataSource->getMethod()->getName()));

                    $candidateProperty->setDataSource($dataSource);
                }
            }
        }
    }

    private function compilePropertiesConditionals() : void
    {
        foreach ($this->candidatesProperties as $candidateProperties) {
            foreach ($candidateProperties->items as $candidateProperty) {
                if ($candidateProperty->hasConditionalRef() && null !== ($conditional = $this->findConditional($candidateProperty->getConditionalRef()))) {

                    //Move this in class "Method"
                    if ($conditional instanceof Conditional\Method) {
                        $reflClass = new \ReflectionClass($conditional->getMethod()->getClass());
                        $conditional->getMethod()->setReflector($reflClass->getMethod($conditional->getMethod()->getName()));
                    }

                    $candidateProperty->setConditional($conditional);
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
        foreach ($this->candidatesProperties as $propertyName => $candidateProperties) {
            $basicCandidateProperties = new ClassMetadata\CandidateProperties;
            $basicCandidateProperties->name = $propertyName;
            $basicCandidateProperties->variables = $candidateProperties->variables;
            $basicCandidateProperties->items = array_filter($candidateProperties->items, fn($property) => ! $property->hasDataSource());

            $this->basicCandidatesProperties[$propertyName] = $basicCandidateProperties;
        }
    }

    private function computeLoadableManagedPropertiesVersions() : void
    {
        foreach ($this->candidatesProperties as $propertyName => $candidateProperties) {
            $loadableCandidateProperties = new ClassMetadata\CandidateProperties;
            $loadableCandidateProperties->name = $propertyName;
            $loadableCandidateProperties->variables = $candidateProperties->variables;
            $loadableCandidateProperties->items = array_filter($candidateProperties->items, fn($property) => ! $property->hasDataSource());

            $this->loadableCandidatesProperties[$propertyName] = $loadableCandidateProperties;
        }
    }

    public function setPropertiesExcludedByDefault(bool $value = true) : self
    {
        $this->propertiesExcludedByDefault = $value;
        return $this;
    }

    public function addExplicitlyIncludedProperty(string $propertyName, ClassMetadata\CandidateProperties $candidateProperties) : self
    {
        $this->explicitlyIncludedProperties[$propertyName] = $candidateProperties;
        return $this;
    }

    public function addExplicitlyExcludedProperty(string $propertyName, ClassMetadata\ExcludedProperty $excludedProperty) : self
    {
        $this->explicitlyExcludedProperties[$propertyName] = $excludedProperty;
        return $this;
    }

    public function setDataSources(ClassMetadata\DataSources $dataSources) : self
    {
        $this->dataSources = $dataSources;
        return $this;
    }

    public function addDataSource(ClassMetadata\DataSource $dataSource) : self
    {
        $this->dataSources->items[] = $dataSource;
        return $this;
    }

    public function setConditionals(ClassMetadata\Conditionals $conditionals) : self
    {
        $this->conditionals = $conditionals;
        return $this;
    }

    public function addConditional(ClassMetadata\Conditional $conditional) : self
    {
        $this->conditionals->items[] = $conditional;
        return $this;
    }

    public function getBeforeUsingLoadedMetadata() : ?StdClassMetadata\Methods
    {
        return $this->beforeUsingLoadedMetadata;
    }

    public function getAfterUsingLoadedMetadata() : ?StdClassMetadata\Methods
    {
        return $this->afterUsingLoadedMetadata;
    }

    public function getBeforeHydration() : ?StdClassMetadata\Methods
    {
        return $this->beforeHydration;
    }

    public function getAfterHydration() : ?StdClassMetadata\Methods
    {
        return $this->afterHydration;
    }
}
