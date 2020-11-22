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
    public bool $defaultHydrateAllProperties = true;
    /**
     * @internal
     */
    public ?string $defaultAdderNameFormat = 'add%sItem';
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

    private array $propertiesCandidates = [];
    private array $basicPropertiesCandidates = [];
    private array $loadablePropertiesCandidates = [];
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

    public function getBasicPropertiesCandidates() : array
    {
        return $this->basicPropertiesCandidates;
    }

    public function getLoadablePropertiesCandidates() : array
    {
        return $this->loadablePropertiesCandidates;
    }

    public function getPropertyCandidates($propertyName) : ClassMetadata\PropertyCandidates
    {
        return $this->propertiesCandidates[$propertyName];
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
        if ($this->defaultHydrateAllProperties) {//default hydrate ALL properties except those specified
            foreach ($this->reflectionClass->getProperties() as $reflectionProperty) {
                if ('__registered' === ($name = $reflectionProperty->getName())) {
                    continue;
                }

                if (isset($this->explicitlyIncludedProperties[$name])) {
                    //Property has a config.
                    //We will hydrate it.
                    foreach ($this->explicitlyIncludedProperties[$name]->items as $candidateProperty) {
                        $candidateProperty->compile($reflectionProperty, [
                            'default_adder_name_format' => $this->defaultAdderNameFormat
                        ]);
                    }

                    $this->propertiesCandidates[$name] = $this->explicitlyIncludedProperties[$name];
                } elseif (! isset($this->explicitlyExcludedProperties[$name])) {
                    //Property has no config .
                    //We will hydrate it because we default hydrate a property except if it is not explicitly excluded.
                    //We create a default config.
                    $propertyCandidates = new ClassMetadata\PropertyCandidates;
                    $propertyCandidates->name = $name;
                    $propertyCandidates->items[] = (new ClassMetadata\Property)->compile(
                        $reflectionProperty, [
                            'default_adder_name_format' => $this->defaultAdderNameFormat
                        ]
                    );

                    $this->propertiesCandidates[$name] = $propertyCandidates;
                }
            }
        } else {//default hydrate NO property except those specified
            foreach ($this->reflectionClass->getProperties() as $reflectionProperty) {
                if ('__registered' === ($name = $reflectionProperty->getName())) {
                    continue;
                }

                if (isset($this->explicitlyIncludedProperties[$name])) {
                    //Property has a config.
                    //We will hydrate it.
                    foreach ($this->explicitlyIncludedProperties[$name]->items as $candidateProperty) {
                        $candidateProperty->compile($reflectionProperty, [
                            'default_adder_name_format' => $this->defaultAdderNameFormat
                        ]);
                    }

                    $this->propertiesCandidates[$name] = $this->explicitlyIncludedProperties[$name];
                }

                //Property has no config.
                //We ignore it because we default ignore all properties.
            }
        }
    }

    private function compilePropertiesDataSources() : void
    {
        foreach ($this->propertiesCandidates as $propertyCandidates) {
            foreach ($propertyCandidates->items as $candidateProperty) {
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
        foreach ($this->propertiesCandidates as $propertyCandidates) {
            foreach ($propertyCandidates->items as $candidateProperty) {
                if ($candidateProperty->hasConditionalRef() && null !== ($conditional = $this->findConditional($candidateProperty->getConditionalRef()))) {

                    //Move this in class "Method"
                    $conditionalValue = $conditional->getValue();
                    if ($conditionalValue instanceof Conditional\Method) {
                        $reflClass = new \ReflectionClass($conditionalValue->getClass());
                        $conditionalValue->setReflector($reflClass->getMethod($conditionalValue->getName()));
                    }

                    $candidateProperty->setConditional($conditional);
                }
            }
        }
    }

    private function compute() : void
    {
        $this->computeBasicPropertyCandidates();
        $this->computeLoadablePropertyCandidates();
    }

    private function computeBasicPropertyCandidates() : void
    {
        foreach ($this->propertiesCandidates as $propertyName => $propertyCandidates) {
            $basicPropertyCandidatesItems = array_filter($propertyCandidates->items, fn($property) => ! $property->hasDataSource());
            if (! count($basicPropertyCandidatesItems)) {
                continue;
            }

            $basicPropertyCandidates = new ClassMetadata\PropertyCandidates;
            $basicPropertyCandidates->name = $propertyName;
            $basicPropertyCandidates->variables = $propertyCandidates->variables;
            $basicPropertyCandidates->items = $basicPropertyCandidatesItems;

            $this->basicPropertiesCandidates[$propertyName] = $basicPropertyCandidates;
        }
    }

    private function computeLoadablePropertyCandidates() : void
    {
        foreach ($this->propertiesCandidates as $propertyName => $propertyCandidates) {
            $loadablePropertyCandidatesItems = array_filter($propertyCandidates->items, fn($property) => $property->hasDataSource());
            if (! count($loadablePropertyCandidatesItems)) {
                continue;
            }

            $loadablePropertyCandidates = new ClassMetadata\PropertyCandidates;
            $loadablePropertyCandidates->name = $propertyName;
            $loadablePropertyCandidates->variables = $propertyCandidates->variables;
            $loadablePropertyCandidates->items = $loadablePropertyCandidatesItems;

            $this->loadablePropertiesCandidates[$propertyName] = $loadablePropertyCandidates;
        }
    }

    public function setDefaultHydrateAllProperties(bool $value) : self
    {
        $this->defaultHydrateAllProperties = $value;
        return $this;
    }

    public function addExplicitlyIncludedProperty(string $propertyName, ClassMetadata\PropertyCandidates $propertyCandidates) : self
    {
        $this->explicitlyIncludedProperties[$propertyName] = $propertyCandidates;
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

    /*public function addDataSource(ClassMetadata\DataSource $dataSource) : self
    {
        $this->dataSources->items[] = $dataSource;
        return $this;
    }*/

    public function setConditionals(ClassMetadata\Conditionals $conditionals) : self
    {
        $this->conditionals = $conditionals;
        return $this;
    }

    /*public function addConditional(ClassMetadata\Conditional $conditional) : self
    {
        $this->conditionals->items[] = $conditional;
        return $this;
    }*/

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
