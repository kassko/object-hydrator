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
class Class_
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
     * @var \Big\Hydrator\ClassMetadata\Model\Methods
     */
    public ?StdClassMetadata\Model\Methods $beforeUsingLoadedMetadata = null;
    /**
     * @internal
     * @var \Big\Hydrator\ClassMetadata\Model\Methods
     */
    public ?StdClassMetadata\Model\Methods $afterUsingLoadedMetadata = null;
    /**
     * @internal
     * @var \Big\Hydrator\ClassMetadata\Model\Methods
     */
    public ?StdClassMetadata\Model\Methods $beforeHydration = null;
    /**
     * @internal
     * @var \Big\Hydrator\ClassMetadata\Model\Methods
     */
    public ?StdClassMetadata\Model\Methods $afterHydration = null;

    private array $explicitlyIncludedProperties = [];
    private array $explicitlyExcludedProperties = [];
    //=== Original metadata: end ===//

    private ?string $rawDataKeyStyle = null;
    private ?Method $toRawDataKeyStyleConverter = null;
    private array $propertiesCandidates = [];
    private array $basicPropertiesCandidates = [];
    private array $loadablePropertiesCandidates = [];
    private ClassMetadata\Model\DataSources $dataSources;
    private ClassMetadata\Model\Discriminators $discriminators;
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

    public function setRawDataKeyStyle(string $setRawDataKeyStyle)
    {
        $this->rawDataKeyStyle = $setRawDataKeyStyle;
    }

    public function setToRawDataKeyStyleConverter(Method $toRawDataKeyStyleConverter)
    {
        $this->toRawDataKeyStyleConverter = $toRawDataKeyStyleConverter;
    }

    public function getBasicPropertiesCandidates() : array
    {
        return $this->basicPropertiesCandidates;
    }

    public function getLoadablePropertiesCandidates() : array
    {
        return $this->loadablePropertiesCandidates;
    }

    public function getPropertyCandidates($propertyName) : ClassMetadata\Model\PropertyCandidates
    {
        return $this->propertiesCandidates[$propertyName];
    }

    public function findDataSource(string $id) : ClassMetadata\Model\DataSource
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

    public function findDiscriminator(string $id) : object
    {
        foreach ($this->discriminators->items as $key => $discriminator) {
            if ($id === $discriminator->id) {
                return $discriminator;
            }
        }

        throw new \LogicException(sprintf('Cannot find discriminator "%s".', $id));
    }

    private function compile() : void
    {
        $this->compileRawDataKeys();
        $this->compileProperties();
        $this->compilePropertiesAttributes();
    }

    private function compileRawDataKeys() : void
    {
        foreach ($this->explicitlyIncludedProperties->items as $name => $explicitlyIncludedProperty) {
            /*if (null !== $this->explicitlyIncludedProperty->keyInRawData) {
                $this->rawDataKeys[$name] = $this->explicitlyIncludedProperty->keyInRawData;
            } else*/if (null !== $this->toRawDataKeyStyleConverter) {
                //$this->rawDataKeys[$name] = $this->toRawDataKeyStyleConverter;
            } elseif (null !== $this->rawDataKeyStyle) {
                switch ($this->rawDataKeyStyle) {
                    case RawDataKeyStyleEnum::RAW_DATA_KEY_STYLE_UNDERSCORE:
                        $this->explicitlyIncludedProperty->keyInRawData = $this->camelCaseToUnderscoreCase($name);
                        //$this->rawDataKeys[$name] = $this->camelCaseToUnderscoreCase($name);
                        break;
                    case RawDataKeyStyleEnum::RAW_DATA_KEY_STYLE_DASH:
                        $this->explicitlyIncludedProperty->keyInRawData = $this->camelCaseToDashCase($name);
                        //$this->rawDataKeys[$name] = $this->camelCaseToDashCase($name);
                        break;
                    case RawDataKeyStyleEnum::RAW_DATA_KEY_STYLE_CAMEL_CASE:
                        $this->explicitlyIncludedProperty->keyInRawData = $name;
                        //$this->rawDataKeys[$name] = $name;
                        break;
                }
            }
        }
    }

    private function camelCaseToUnderscoreCase(string $str) : string
    {
        return $this->camelCaseToSeparatorCase($str, '_');
    }

    private function camelCaseToDashCase(string $str) : string
    {
        return $this->camelCaseToSeparatorCase($str, '-');
    }

    private function camelCaseToSeparatorCase(string $str, string $separator) : string
    {
        $pattern = '!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!';
        preg_match_all($pattern, $str, $matches);
        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ?
                strtolower($match) :
                lcfirst($match);
        }

        return implode('_', $ret);
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
                    $propertyCandidates = new ClassMetadata\Model\PropertyCandidates;
                    $propertyCandidates->name = $name;

                    $extraData = [];
                    if ('array' !== $reflectionProperty->getType()->getName()) {
                        $property = new ClassMetadata\Model\Property;
                    } else {
                        $property = new ClassMetadata\Model\Property\CollectionType;
                        $extraData = ['default_adder_name_format' => $this->defaultAdderNameFormat];
                    }

                    $propertyCandidates->items[] = $property->compile(
                        $reflectionProperty,
                        $extraData
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

    private function compilePropertiesAttributes() : void
    {
        foreach ($this->propertiesCandidates as $propertyCandidates) {
            foreach ($propertyCandidates->items as $candidateProperty) {
                $this->compilePropertyDataSources($candidateProperty);
                $this->compilePropertyDiscriminators($candidateProperty);
                if ($candidateProperty->isCollection()) {
                    $this->compilePropertyItemClassCandidates($candidateProperty);
                }
            }
        }
    }

    private function compilePropertyDataSources(ClassMetadata\Model\Property\Leaf $property) : void
    {
        if (null !== $property->dataSource) {
            if (null !== $property->dataSourceRef) {
                throw new \LogicException(sprintf(
                    'Cannot interpret properly config of property "%s" of object "%s".' .
                    PHP_EOL . 'You must use either a data source inline or a reference to a data source id but not both.' .
                    PHP_EOL . 'Given both a data source "%s" and a data source reference "%s".' .
                    PHP_EOL . 'Please fix this.',
                    $property->getName(),
                    $this->reflectionClass->getName(),
                    $property->dataSource->id,
                    $property->dataSourceRef
                ));
            }
            return;
        }

        if (null !== $property->dataSourceRef) {
            if (null === ($dataSource = $this->findDataSource($property->dataSourceRef))) {
                throw new \LogicException(sprintf(
                    'Cannot interpret properly config of property "%s" object "%s".' .
                    PHP_EOL . 'Cannot find a data source with a such reference "%s".',
                    $property->getName(),
                    $this->reflectionClass->getName(),
                    $property->dataSourceRef
                ));
            }
            //Move this in class "Method"
            $reflClass = new \ReflectionClass($dataSource->method->class);
            $dataSource->method->setReflector($reflClass->getMethod($dataSource->method->name));

            $property->setDataSource($dataSource);
        }
    }

    private function compilePropertyDiscriminators(ClassMetadata\Model\Property\Leaf $property) : void
    {
        if (null !== $property->discriminator) {
            if (null !== $property->discriminatorRef) {
                throw new \LogicException(sprintf(
                    'Cannot interpret properly config of property "%s" of object "%s".' .
                    PHP_EOL . 'You must use either a discriminator inline or a reference to a discriminator id but not both.' .
                    PHP_EOL . 'Given both a discriminator inline "%s" and a discriminator reference "%s".' .
                    PHP_EOL . 'Please fix this.',
                    $property->getName(),
                    $this->reflectionClass->getName(),
                    $property->discriminator->id,
                    $property->discriminatorRef
                ));
            }
            return;
        }

        if (null !== $property->discriminatorRef) {
            if (null === ($discriminator = $this->findDiscriminator($property->discriminatorRef))) {
                throw new \LogicException(sprintf(
                    'Cannot interpret properly config of property "%s" object "%s".' .
                    PHP_EOL . 'Cannot find a discriminator with a such reference "%s".',
                    $property->getName(),
                    $this->reflectionClass->getName(),
                    $property->discriminatorRef
                ));
            }

            //Move this in class "Method"
            $discriminatorValue = $discriminator->getValue();
            if ($discriminatorValue instanceof Discriminator\Method) {
                $reflClass = new \ReflectionClass($discriminatorValue->getClass());
                $discriminatorValue->setReflector($reflClass->getMethod($discriminatorValue->name));
            }

            $property->setDiscriminator($discriminator);
        }
    }

    private function compilePropertyItemClassCandidates(ClassMetadata\Model\Property\Leaf $property) : void
    {
        if (null !== $property->itemClassCandidates) {
            if (null !== $property->itemClassCandidate) {
                throw new \LogicException(sprintf(
                    'Cannot interpret properly config of property "%s" of object "%s".' .
                    PHP_EOL . 'You must use either a single itemClassCandidate or a collection of itemClassCandidates but not both.',
                    $property->getName(),
                    $this->reflectionClass->getName()
                ));
            }
            return;
        }

        if ($property->itemClassCandidate) {
            $property->itemClassCandidates = new ItemClassCandidates;
            $property->itemClassCandidates->items[] = $property->itemClassCandidate;
            $property->itemClassCandidate = null;
        }
    }

    /*private function compilePropertiesDataSources() : void
    {
        foreach ($this->propertiesCandidates as $propertyCandidates) {
            foreach ($propertyCandidates->items as $candidateProperty) {
                if ($candidateProperty->hasDataSource()) {
                    if ($candidateProperty->hasDataSourceRef()) {
                        throw new \LogicException(sprintf(
                            'Cannot interpret properly config of property "%s" object "%s".' .
                            PHP_EOL . 'You must use either a data source inline or a reference to a data source id but not both.' .
                            PHP_EOL . 'Given both a data source "%s" and a data source reference "%s".'
                            PHP_EOL . 'Please fix this.',
                            $propertyCandidates->getName(),
                            $this->reflectionClass->getName(),
                            $candidateProperty->getDataSource()->getId(),
                            $candidateProperty->dataSourceRef
                        ));
                    }
                    continue;
                }

                if ($candidateProperty->hasDataSourceRef()) {
                    if (null === ($dataSource = $this->findDataSource($candidateProperty->getDataSourceRef()))) {
                        throw new \LogicException(sprintf(
                            'Cannot interpret properly config of property "%s" object "%s".' .
                            PHP_EOL . 'Cannot find a data source with a such reference "%s".',
                            $propertyCandidates->getName(),
                            $this->reflectionClass->getName(),
                            $candidateProperty->getDataSourceRef()
                        ));
                    }
                    //Move this in class "Method"
                    $reflClass = new \ReflectionClass($dataSource->getMethod()->getClass());
                    $dataSource->getMethod()->setReflector($reflClass->getMethod($dataSource->getMethod()->getName()));

                    $candidateProperty->setDataSource($dataSource);
                }
            }
        }
    }*/

    /*private function compilePropertiesDiscriminators() : void
    {
        foreach ($this->propertiesCandidates as $propertyCandidates) {
            foreach ($propertyCandidates->items as $candidateProperty) {
                if ($candidateProperty->hasDiscriminator()) {
                    if ($candidateProperty->hasDiscriminatorRef()) {
                        throw new \LogicException(sprintf(
                            'Cannot interpret properly config of property "%s" object "%s".' .
                            PHP_EOL . 'You must use either a discriminator inline or a reference to a discriminator id but not both.' .
                            PHP_EOL . 'Given both a discriminator inline "%s" and a discriminator reference "%s".'
                            PHP_EOL . 'Please fix this.',
                            $propertyCandidates->getName(),
                            $this->reflectionClass->getName(),
                            $candidateProperty->getDiscriminator()->getId(),
                            $candidateProperty->discriminatorRef
                        ));
                    }
                    continue;
                }

                if ($candidateProperty->hasDiscriminatorRef()) {
                    if (null === ($discriminator = $this->findDiscriminator($candidateProperty->getDiscriminatorRef()))) {
                        throw new \LogicException(sprintf(
                            'Cannot interpret properly config of property "%s" object "%s".' .
                            PHP_EOL . 'Cannot find a discriminator with a such reference "%s".',
                            $propertyCandidates->getName(),
                            $this->reflectionClass->getName(),
                            $candidateProperty->getDiscriminatorRef()
                        ));
                    }

                    //Move this in class "Method"
                    $discriminatorValue = $discriminator->getValue();
                    if ($discriminatorValue instanceof Discriminator\Method) {
                        $reflClass = new \ReflectionClass($discriminatorValue->getClass());
                        $discriminatorValue->setReflector($reflClass->getMethod($discriminatorValue->getName()));
                    }

                    $candidateProperty->setDiscriminator($discriminator);
                }
            }
        }
    }*/

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

            $basicPropertyCandidates = new ClassMetadata\Model\PropertyCandidates;
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

            $loadablePropertyCandidates = new ClassMetadata\Model\PropertyCandidates;
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

    public function addExplicitlyIncludedProperty(string $propertyName, ClassMetadata\Model\PropertyCandidates $propertyCandidates) : self
    {
        $this->explicitlyIncludedProperties[$propertyName] = $propertyCandidates;
        return $this;
    }

    public function addExplicitlyExcludedProperty(string $propertyName, ClassMetadata\ExcludedProperty $excludedProperty) : self
    {
        $this->explicitlyExcludedProperties[$propertyName] = $excludedProperty;
        return $this;
    }

    public function setDataSources(ClassMetadata\Model\DataSources $dataSources) : self
    {
        $this->dataSources = $dataSources;
        return $this;
    }

    /*public function addDataSource(ClassMetadata\Model\DataSource $dataSource) : self
    {
        $this->dataSources->items[] = $dataSource;
        return $this;
    }*/

    public function setDiscriminators(ClassMetadata\Model\Discriminators $discriminators) : self
    {
        $this->discriminators = $discriminators;
        return $this;
    }

    /*public function addDiscriminator(ClassMetadata\Model\Discriminator $discriminator) : self
    {
        $this->discriminators->items[] = $discriminator;
        return $this;
    }*/

    public function getAfterMetadataLoading() : ?StdClassMetadata\Model\Methods
    {
        return $this->beforeUsingLoadedMetadata;
    }

    public function getAfterUsingLoadedMetadata() : ?StdClassMetadata\Model\Methods
    {
        return $this->afterUsingLoadedMetadata;
    }

    public function getBeforeHydration() : ?StdClassMetadata\Model\Methods
    {
        return $this->beforeHydration;
    }

    public function getAfterHydration() : ?StdClassMetadata\Model\Methods
    {
        return $this->afterHydration;
    }
}
