<?php

namespace Kassko\ObjectHydrator;

use Kassko\ObjectHydrator\ClassMetadata\{Model, ReflectionClass, Repository};
use Kassko\ObjectHydrator\MethodInvoker;
use Doctrine\Common\CollectionType\ArrayCollection;
use Symfony\Component\Config\Definition\Processor;

class ModelBuilder
{
    private string $class;
    private array $configs = [];
    private ReflectionClass $reflectionClass;

    private Repository\DataSource $dataSourceRepository;
    private Repository\Expression $expressionRepository;
    private Repository\Method $methodRepository;
    private Repository\ReflectionClass $reflectionClassRepository;
    private MethodInvoker $methodInvoker;

    public function __construct(
        Repository\DataSource $dataSourceRepository,
        Repository\Expression $expressionRepository,
        Repository\Method $methodRepository,
        Repository\ReflectionClass $reflectionClassRepository,
        MethodInvoker $methodInvoker
    ) {
        $this->dataSourceRepository = $dataSourceRepository;
        $this->expressionRepository = $expressionRepository;
        $this->methodRepository = $methodRepository;
        $this->reflectionClassRepository = $reflectionClassRepository;
        $this->methodInvoker = $methodInvoker;
    }

    public function setClass(string $class) : self
    {
        $this->class = $class;

        return $this;
    }

    public function addConfig(array $config) : self
    {
        $this->configs[] = $config;

        return $this;
    }

    public function setConfigs(array $configs) : self
    {
        $this->configs = $configs;

        return $this;
    }

    private function getValidatedConfig() : array
    {
        $processor = new Processor();
        $classMetadataValidator = new ClassMetadataValidator;

        $validatedConfig = $processor->processConfiguration(
            $classMetadataValidator,
            $this->configs
        );

        return $validatedConfig;
    }

    public function build() : Model\Class_
    {
        $arrayMetadata = $this->getValidatedConfig();

        $classMetadata = new Model\Class_($this->class, $this->reflectionClassRepository);
        $this->reflectionClass = $classMetadata->getReflectionClass();


        if (isset($arrayMetadata['class'])) {
            $classMetadata->setAccessorsToBypass($arrayMetadata['class']['accessors_to_bypass']);
        }

        if (isset($arrayMetadata['data_sources']) &&  count($arrayMetadata['data_sources'])) {
            $dataSources = $this->buildDataSources($arrayMetadata['data_sources']);

            foreach ($dataSources as $key => $dataSource) {
                $this->dataSourceRepository->add($dataSource);
            }
        } elseif (isset($arrayMetadata['data_source']) &&  count($arrayMetadata['data_source'])) {
            $this->dataSourceRepository->add($this->buildDataSource($arrayMetadata['data_source']));
        }

        if (isset($arrayMetadata['methods']) && count($arrayMetadata['methods'])) {
            $methods = $this->buildMethods($arrayMetadata['methods']);

            foreach ($methods as $key => $method) {
                $this->methodRepository->add($method);
            }
        } elseif (isset($arrayMetadata['method']) &&  count($arrayMetadata['method'])) {
            $this->methodRepository->add($this->buildMethod($arrayMetadata['method']));
        }

        if (isset($arrayMetadata['expressions']) && count($arrayMetadata['expressions'])) {
            $expressions = $this->buildExpressions($arrayMetadata['expressions']);

            foreach ($expressions as $key => $expression) {
                $this->expressionRepository->add($expression);
            }
        } elseif (isset($arrayMetadata['expression']) &&  count($arrayMetadata['expression'])) {
            $this->expressionRepository->add($this->buildExpression($arrayMetadata['expression']));
        }


        foreach ($this->reflectionClass->getProperties() as $reflectionProperty) {
            $propertyName = $reflectionProperty->getName();
            if ('__registered' === $propertyName) {
                continue;
            }

            $property = null;

            if (isset($arrayMetadata['properties'][$propertyName])) {
                $property = $this->buildPropertyKind(
                    $propertyName,
                    $arrayMetadata['properties'][$propertyName],
                    $arrayMetadata['class'], $reflectionProperty
                );
            } elseif (true === $arrayMetadata['class']['default_autoconfigure_properties']
                && ! in_array($propertyName, $arrayMetadata['not_to_autoconfigure_properties'])) {

                if ('array' !== $reflectionProperty->getType()->getName()) {//check doc comment too, and type like MyClass[]
                    $property = new Model\Property\SingleType($propertyName);
                } else {
                    $property = new Model\Property\CollectionType($propertyName);
                }

                $this->buildBaseProperty($property, [], [], $reflectionProperty);

                $property->setKeyInRawData($this->resolveKeyInRawData(null, $propertyName, $arrayMetadata['class']));
            }

            if (null !== $property) {
                $classMetadata->addProperty($property);
            }
        }

        return $classMetadata;
    }

    private function buildPropertyKind(string $propertyName, array $propertyData, array $classData, \ReflectionProperty $reflectionProperty)
    {
        $propertyKinds = [];

        if ($this->isEnabled('single_type', $propertyData)) {
            $propertyKinds[] = 'single_type';
        }

        if ($this->isEnabled('collection_type', $propertyData)) {
            $propertyKinds[] = 'collection_type';
        }

        if ($this->isEnabled('candidates', $propertyData)) {
            $propertyKinds[] = 'candidates';
        }

        if (count($propertyKinds) > 1) {
            throw new \LogicException(sprintf(
                'Cannot build model of property "%s".' .
                PHP_EOL . 'You must configure only one kind of property.' .
                PHP_EOL . 'Given several kinds [%s].',
                $propertyName,
                implode(',', $propertyKinds)
            ));
        }

        if ($this->isEnabled('single_type', $propertyData)) {
            $property = $this->buildBasicProperty($propertyName, $propertyData['single_type'], $classData, $reflectionProperty);
        } elseif ($this->isEnabled('collection_type', $propertyData)) {
            $property = $this->buildCollectionProperty($propertyName, $propertyData['collection_type'], $classData, $reflectionProperty);
        } elseif ($this->isEnabled('candidates', $propertyData)) {
            $property = $this->buildCandidatesProperty($propertyName, $propertyData['candidates'], $classData, $reflectionProperty);
        }

        return $property;
    }

    private function resolveKeyInRawData(?string $keyInRawData, string $propertyName, ?array $classData) : string
    {
        if (null !== $keyInRawData) {
            return $keyInRawData;
        }

        if (isset($classData['raw_data_key_style'])) {
            switch ($classData['raw_data_key_style']) {
                case Model\Enum\RawDataKeyStyle::UNDERSCORE:
                    return $this->camelCaseToUnderscoreCase($propertyName);
                case Model\Enum\RawDataKeyStyle::DASH:
                    return $this->camelCaseToDashCase($propertyName);
                case Model\Enum\RawDataKeyStyle::CAMEL_CASE:
                    return $propertyName;
                case Model\Enum\RawDataKeyStyle::CUSTOM:
                    if (null !== $classData['raw_data_key_style_converter']) {
                        return $this->methodInvoker->invokeMethod(
                            $this->buildMethod($classData['raw_data_key_style_converter']),
                            [$propertyName]
                        );
                    }
            }
        }

        return $propertyName;
    }

    private function isEnabled(string $key, ?array $config) : bool
    {
        return isset($config) && isset($config[$key]['enabled']) && $config[$key]['enabled'];
    }

    private function hasCount(string $key, ?array $config) : bool
    {
        return isset($config) && isset($config[$key]) && count($config[$key]);
    }

    private function buildBaseProperty(Model\Property $property, array $propertyData, array $classData, \ReflectionProperty $reflectionProperty)
    {
        if (
            isset($classData['raw_data_key_style'])
            && $classData['raw_data_key_style'] !== Model\Enum\RawDataKeyStyle::CUSTOM
            && $this->isEnabled('raw_data_key_style_converter', $classData)
        ) {
            throw new \LogicException(sprintf(
                'Cannot build model of property "%s"' .
                PHP_EOL . 'Given the attribute "raw_data_key_style" not custom but the attribute to customize "raw_data_key_style_converter" is set.',
                $property->getName()
            ));
        }

        $property->setKeyInRawData($this->resolveKeyInRawData($propertyData['key_in_raw_data'], $property->getName(), $classData));

        if (isset($propertyData['class']))  {
            $property->setClass($propertyData['class']);
            $property->setType('object');
        } elseif ($reflectionProperty->hasType()) {
            if (!isset($propertyData['class']) && false === $reflectionProperty->getType()->isBuiltIn()) {
                $property->setClass($reflectionProperty->getType()->getName());
                $property->setType('object');
            }

            if (null === $property->getType() && true === $reflectionProperty->getType()->isBuiltIn()) {
                $property->setType($reflectionProperty->getType()->getName());
            }
        }

        if (isset($propertyData['getter'])) {
            $getter = $propertyData['getter'];
        } else {
            $getter = $this->getterise($reflectionProperty->getName());
        }
        if (null !== $getter) {
            $property->setGetter($getter);
        }

        if (isset($propertyData['setter'])) {
            $setter = $propertyData['setter'];
        } else {
            $setter = $this->setterise($reflectionProperty->getName());
        }
        if (null !== $setter) {
            $property->setSetter($setter);
        }

        if ($this->isEnabled('data_source', $propertyData)) {
            $property->setDataSource($this->buildDataSource($propertyData['data_source']));
        } elseif (isset($propertyData['data_source_ref'])) {
            $property->setDataSource($this->dataSourceRepository->find($propertyData['data_source_ref']));
        }

        if ($this->isEnabled('discriminator_method', $propertyData)) {
            $property->setDiscriminator($this->buildMethod($propertyData['discriminator_method']));
        } elseif (isset($propertyData['discriminator_method_ref'])) {
            $property->setDiscriminator($this->methodRepository->find($propertyData['discriminator_method_ref']));
        }

        if ($this->isEnabled('discriminator_expression', $propertyData)) {
            $property->setDiscriminator($this->buildExpression($propertyData['discriminator_expression']));
        } elseif (isset($propertyData['discriminator_expression_ref'])) {
            $property->setDiscriminator($this->expressionRepository->find($propertyData['discriminator_expression_ref']));
        }

        if ($this->isEnabled('raw_data_location', $propertyData)) {
            $property->setRawDataLocation($this->buildRawDataLocation($propertyData['raw_data_location']));
        }

        if ($this->isEnabled('instance_creation', $propertyData)) {
            $property->setInstanceCreation($this->buildInstanceCreation($propertyData['instance_creation']));
        }

        if (isset($propertyData['default_value'])) {
            $property->setDefaultValue($propertyData['default_value']);
        }

        if ($this->hasCount('variables', $propertyData)) {
            $property->setVariables($propertyData['variables']);
        }

        if ($this->hasCount('dynamic_attributes', $propertyData)) {
            $property->setDynamicAttributes($propertyData['dynamic_attributes']);
        }

        if ($this->isEnabled('callbacks_using_metadata', $propertyData)) {
            $property->setCallbackUsingMetadata($this->buildCallbacks($propertyData['callbacks_using_metadata']));
        }

        if ($this->isEnabled('callbacks_hydration', $propertyData)) {
            $property->setCallbackHydration($this->buildCallbacks($propertyData['callbacks_hydration']));
        }

        if ($this->isEnabled('callbacks_assigning_hydrated_value', $propertyData)) {
            $property->setCallbacksAssigningHydratedValue($this->buildCallbacks($propertyData['callbacks_assigning_hydrated_value']));
        }

        return $property;
    }

    private function buildBasicProperty(
        string $propertyName,
        array $propertyData,
        array $classData,
        \ReflectionProperty $reflectionProperty
    ) : Model\Property\SingleType {
        $property = new Model\Property\SingleType($propertyName);

        $property = $this->buildBaseProperty($property, $propertyData, $classData, $reflectionProperty);

        if ($reflectionProperty->hasType()) {
            if (null !== $property->getType() && 'array' === $property->getType()) {//check also classes ending with []
                throw new \LogicException(sprintf(
                    'Cannot interpret properly config of property "%s".' .
                    PHP_EOL . 'Cannot have a property configured as "property" and typed as "array".' .
                    PHP_EOL . 'Either the php type "array" is wrong or must use a property collection.',
                    $reflectionProperty->getName()
                ));
            }
        }

        return $property;
    }

    private function buildCollectionProperty(string $propertyName, array $propertyData, array $classData, \ReflectionProperty $reflectionProperty) : Model\Property\CollectionType
    {
        $property = new Model\Property\CollectionType($propertyName);

        $property = $this->buildBaseProperty($property, $propertyData, $classData, $reflectionProperty);

        /*if ($reflectionProperty->hasType()) {
            if (!$property->isCollection() && true === $reflectionProperty->getType()->isBuiltIn()) {
                if ('array' === $reflectionProperty->getType()->getName()) {
                    $property->setCollection(true);
                }
            }
        }*/

        if (isset($propertyData['items_class'])) {
            $property->setItemsClass($propertyData['items_class']);
        }

        if (isset($propertyData['item_class_candidates'])) {
            foreach ($this->buildItemsClassCandidate($propertyData['item_class_candidates']) as $itemClassCandidate) {
                $property->addItemClassCandidate($itemClassCandidate);
            }
        }


        $adder = null;
        if (isset($propertyData['adder'])) {
            $adder = $propertyData['adder'];
        } elseif (isset($classData['default_adder_name_format'])) {
            $adder = $this->adderise($reflectionProperty->getName(), $classData['default_adder_name_format']);
        }
        if (null !== $adder) {
            $property->setAdder($adder);
        }

        return $property;
    }

    private function buildCandidatesProperty(
        string $propertyName,
        array $propertyCandidatesData,
        array $classData,
        \ReflectionProperty $reflectionProperty
    ) : Model\Property {
        $propertyCandidates = new Model\Property\Candidates($propertyName);

        foreach ($propertyCandidatesData['candidates'] as $propertyCandidateData) {
            $propertyCandidates->addCandidate($this->buildPropertyKind($propertyName, $propertyCandidateData, $classData, $reflectionProperty));
        }

        return $propertyCandidates;
    }

    private function buildDefaultProperty(string $propertyName, \ReflectionProperty $reflectionProperty) : Model\Property\Leaf
    {
        if ('array' !== $reflectionProperty->getType()->getName()) {//check doc comment too, and type like MyClass[]
            $property = new Model\Property\SingleType($propertyName);
        } else {
            $property = new Model\Property\CollectionType($propertyName);
        }

        return $property;
    }

    private function buildItemsClassCandidate(array $itemClassCandidatesData) : array
    {
        $itemClassCandidates = [];

        foreach ($itemClassCandidatesData as $itemClassCandidateData) {
            $itemClassCandidates[] = $this->buildItemClassCandidate($itemClassCandidateData);
        }

        return $itemClassCandidates;
    }

    private function buildItemClassCandidate(array $itemClassCandidateData) : Model\ItemClassCandidate
    {
        $itemClassCandidate = new Model\ItemClassCandidate($itemClassCandidateData['class']);

        if ($this->isEnabled('raw_data_location', $itemClassCandidateData)) {
            $itemClassCandidate->setRawDataLocation($this->buildRawDataLocation($itemClassCandidateData['raw_data_location']));
        }

        if ($this->isEnabled('instance_creation', $itemClassCandidateData)) {
            $itemClassCandidate->setInstanceCreation($this->buildInstanceCreation($itemClassCandidateData['instance_creation']));
        }

        if (isset($itemClassCandidateData['discriminator_expression'])) {
            $discriminator = $this->buildExpression($itemClassCandidateData['discriminator_expression']);
        } elseif (isset($itemClassCandidateData['discriminator_method'])) {
            $discriminator = $this->buildMethod($itemClassCandidateData['discriminator_method']);
        } else {
            throw new \Exception(sprintf(
                'Cannot interpret configuration of item candidate: class "%s".',
                $itemClassCandidateData['class']
            ));
        }

        $itemClassCandidate->setDiscriminator($discriminator);

        return $itemClassCandidate;
    }

    private function buildDiscriminator(array $discriminatorData)
    {
        if (null !== $discriminatorData['method']) {
            $discriminator = $this->buildMethod($discriminatorData['method']);
        } else {
            $discriminator = $this->buildExpression($discriminatorData['expression']);
        }
    }

    private function buildDataSources(array $dataSourcesData) : array
    {
        $dataSources = [];

        foreach ($dataSourcesData as $dataSourceData) {
            $dataSources[] = $this->buildDataSource($dataSourceData);
        }

        return $dataSources;
    }

    private function buildDataSource(array $dataSourceData) : Model\DataSource
    {
        $dataSource = new Model\DataSource($dataSourceData['id']);

        $dataSource->setMethod($this->buildMethod($dataSourceData['method']));
        $dataSource->setIndexedByPropertiesKeys($dataSourceData['indexed_by_properties_keys']);
        $dataSource->setLoadingMode($dataSourceData['loading_mode']);
        $dataSource->setLoadingScope($dataSourceData['loading_scope'], $dataSourceData['loading_scope_keys']);

/*
        if (isset($dataSourceData['fallback_data_source'])) {
            $dataSource->setFallbackDataSource($this->buildDataSource($dataSourceData['fallback_data_source']));
        } else*/if (isset($dataSourceData['fallback_data_source_ref'])) {
            $fallbackDataSource = $this->dataSourceRepository->find($dataSourceData['fallback_data_source_ref']);
            $dataSource->setFallbackDataSource($fallbackDataSource);
        }
        if (isset($dataSourceData['callbacks_using_metadata'])) {
            $dataSource->setCallbackUsingMetadata($this->buildCallbacks($dataSourceData['callbacks_using_metadata']));
        }

        if (isset($dataSourceData['callbacks_hydration'])) {
            $dataSource->setCallbackHydration($this->buildCallbacks($dataSourceData['callbacks_hydration']));
        }

        if (isset($dataSourceData['callbacks_data_fetching'])) {
            $dataSource->setCallbacksDataFetching($this->buildCallbacks($dataSourceData['callbacks_data_fetching']));
        }

        if (isset($dataSourceData['callbacks_assigning_hydrated_value'])) {
            $dataSource->setCallbacksAssigningHydratedValue($this->buildCallbacks($dataSourceData['callbacks_assigning_hydrated_value']));
        }

        return $dataSource;
    }

    private function buildCallbacks(array $propertyData) : Model\Callbacks
    {
        $callbacks = new Model\Callbacks;

        if (isset($propertyData['before_collection'])) {
            foreach ($propertyData['before_collection'] as $before) {
                $callbacks->addBefore($this->buildMethod($before));
            }
        } elseif (isset($propertyData['before'])) {
            $callbacks->addBefore($this->buildMethod($propertyData['before']));
        }

        if (isset($propertyData['after_collection'])) {
            foreach ($propertyData['after_collection'] as $after) {
                $callbacks->addAfter($this->buildMethod($after));
            }
        } elseif (isset($propertyData['after'])) {
            $callbacks->addAfter($this->buildMethod($propertyData['after']));
        }

        return $callbacks;
    }

    private function buildMethods(array $methodsData) : array
    {
        $methods = [];

        foreach ($methodsData as $methodData) {
            $methods[] = $this->buildMethod($methodData);
        }

        return $methods;
    }

    private function buildMethod(array $methodData) : Model\Method
    {
        $method = new Model\Method($methodData['id']);

        if (isset($methodData['class'])) {
            $method->setClass($methodData['class']);

            $class = $methodData['class'];
            if ($class === $this->reflectionClass->getName()) {
                $static = $this->reflectionClass->getMethod($methodData['name'])->isStatic();
            } else {
                $reflectionClass = new \ReflectionClass($class);
                $static = $reflectionClass->getMethod($methodData['name'])->isStatic();
            }
            $method->setStatic($static);
        } elseif (isset($methodData['service_key'])) {
            $method->setServiceKey($methodData['service_key']);
        }

        $method->setName($methodData['name']);

        if (isset($methodData['args'])) {
            foreach ($methodData['args'] as $arg) {
                $advancedExpressionDetected = preg_match('/expr\((.+)\)/', $arg, $matches);
                if (1 === $advancedExpressionDetected) {
                    $arg = new Model\Expression($matches[1]);
                }

                $method->addArg($arg);
            }
        }

        if (isset($methodData['magic_call_allowed'])) {
            $method->setMagicCallAllowed($methodData['magic_call_allowed']);
        }

        return $method;
    }

    private function buildExpressions(array $expressionsData) : array
    {
        $expressions = [];

        foreach ($expressionsData as $expressionData) {
            $expressions[] = $this->buildExpression($expressionData);
        }

        return $expressions;
    }

    private function buildExpression(array $expressionData)
    {
        $expression = new Model\Expression($expressionData['id']);
        $expression->setValue($expressionData['value']);

        return $expression;
    }

    private function buildRawDataLocation(array $rawDataLocationData) : Model\RawDataLocation
    {
        $rawDataLocation = new Model\RawDataLocation($rawDataLocationData['location_name']);

        $mappingTechnics = [];

        if ($this->isEnabled('keys_mapping_method', $rawDataLocationData)) {
            $mappingTechnics['keys_mapping_method'];
            $rawDataLocation->setKeysMapping(
                new Model\KeysMapping\Method($this->buildMethod($rawDataLocationData['keys_mapping_method']))
            );
        }

        if ($this->hasCount('keys_mapping_values', $rawDataLocationData)) {
            $mappingTechnics['keys_mapping_values'];
            if (count($mappingTechnics) > 1) {
                throw new \LogicException(sprintf(
                    'Cannot interpret properly raw_data_location configuration.'
                    . PHP_EOL . 'You must provide only one technic of mapping parent data among "values", "prefix" and "method".'
                    . PHP_EOL . 'Given several technics [%s].',
                    implode(',', $mappingTechnics)
                ));
            }
            $rawDataLocation->setKeysMapping(
                new Model\KeysMapping\Values($rawDataLocationData['keys_mapping_values'])
            );
        }

        if (isset($rawDataLocationData['keys_mapping_prefix'])) {
            $mappingTechnics['keys_mapping_prefix'];
            if (count($mappingTechnics) > 1) {
                throw new \LogicException(sprintf(
                    'Cannot interpret properly raw_data_location configuration.'
                    . PHP_EOL . 'You must provide only one technic of mapping parent data among "values", "prefix" and "method".'
                    . PHP_EOL . 'Given several technics [%s].',
                    implode(',', $mappingTechnics)
                ));
            }

            $rawDataLocation->setKeysMapping(
                new Model\KeysMapping\Prefix($rawDataLocationData['keys_mapping_prefix'])
            );
        }

        return $rawDataLocation;
    }

    private function buildInstanceCreation(array $instanceCreationData) : Model\InstanceCreation
    {
        $instanceCreation = new Model\InstanceCreation();

        if (isset($instanceCreationData['factory_method_name'])) {
            $instanceCreation->setFactoryMethodName($instanceCreationData['factory_method_name']);
        } elseif ($this->isEnabled('factory_method', $instanceCreationData)) {
            $instanceCreation->setFactoryMethod($this->buildMethod($instanceCreationData['factory_method']));
        }

        if (isset($instanceCreationData['set_properties_through_creation_method_when_possible'])) {
            $instanceCreation->setSetPropertiesThroughCreationMethodWhenPossible(
                $instanceCreationData['set_properties_through_creation_method_when_possible']
            );
        }

        if (isset($instanceCreationData['always_access_properties_directly'])) {
            $instanceCreation->setAlwaysAccessPropertiesDirectly($instanceCreationData['always_access_properties_directly']);
        }

        if ($this->isEnabled('after_creation_methods', $instanceCreationData)) {
            $methods = $this->buildMethods($instanceCreationData['after_creation_methods']);

            foreach ($methods as $method) {
                $instanceCreation->addAfterCreationMethod($method);
            }
        } elseif ($this->isEnabled('after_creation_method', $instanceCreationData)) {
            $instanceCreation->addAfterCreationMethod($this->buildMethod($arrayMetadata['after_creation_method']));
        }

        return $instanceCreation;
    }

    private function getterise(string $propertyName) : ?string
    {
        static $defaultsGettersTypes = ['get', 'is', 'has'];

        foreach ($defaultsGettersTypes as $getterType) {
            $getter = $getterType.ucfirst($propertyName);
            if ($this->reflectionClass->hasMethod($getter)) {
                return $getter;
            }
        }

        return null;
    }

    private function setterise(string $propertyName) : ?string
    {
        $setter = 'set'.ucfirst($propertyName);

        if ($this->reflectionClass->hasMethod($setter)) {
            return $setter;
        }

        return null;
    }

    private function adderise(string $propertyName, ?string $defaultAdderNameFormat) : ?string
    {
        $adder = sprintf($defaultAdderNameFormat, ucfirst($propertyName));

        if ($this->reflectionClass->hasMethod($adder)) {
            return $adder;
        }

        return null;
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

        return implode($separator, $ret);
    }
}
