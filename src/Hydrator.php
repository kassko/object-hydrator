<?php

namespace Kassko\ObjectHydrator;

use Kassko\ObjectHydrator\ClassMetadata;
use Kassko\ObjectHydrator\ClassMetadata\Model\Enum;

use function array_filter;

class Hydrator
{
    use PropertyCandidatesResolverAwareTrait;

    private ModelLoader $modelLoader;
    private MemberAccessStrategyFactory $memberAccessStrategyFactory;
    private IdentityMap $identityMap;
    private ObjectLoadabilityChecker $objectLoadabilityChecker;
    private DataFetcher $dataFetcher;
    private MethodInvoker $invoker;
    private ExpressionContext $expressionContext;
    private EssentialDataProvider $essentialDataProvider;
    private ExpressionEvaluator $expressionEvaluator;
    private Config $config;

    public function __construct(
        ModelLoader $modelLoader,
        MemberAccessStrategyFactory $memberAccessStrategyFactory,
        IdentityMap $identityMap,
        ObjectLoadabilityChecker $objectLoadabilityChecker,
        DataFetcher $dataFetcher,
        MethodInvoker $methodInvoker,
        ExpressionContext $expressionContext,
        EssentialDataProvider $essentialDataProvider,
        ExpressionEvaluator $expressionEvaluator,
        Config $config
    ) {
        $this->modelLoader = $modelLoader;
        $this->memberAccessStrategyFactory = $memberAccessStrategyFactory;
        $this->identityMap = $identityMap;
        $this->objectLoadabilityChecker = $objectLoadabilityChecker;
        $this->dataFetcher = $dataFetcher;
        $this->methodInvoker = $methodInvoker;
        $this->expressionContext = $expressionContext;
        $this->essentialDataProvider = $essentialDataProvider;
        $this->expressionEvaluator = $expressionEvaluator;
        $this->config = $config;
    }

    public function load(object $object) : void
    {
        $this->objectLoadabilityChecker->checkIfIsLoadable($object);

        $this->hydrateLoadableProperties($object, [], $this->modelLoader->load(get_class($object)), true);
    }

    public function loadProperty(object $object, string $propertyName) : void
    {
        $this->objectLoadabilityChecker->checkIfIsLoadable($object);

        $normalizedData = [];
        $classMetadata = $this->modelLoader->load(get_class($object));
        $properties = $this->resolveProperties($classMetadata->getProperties(), $object, $normalizedData, $classMetadata);
        $loadableProperties = $this->filterProperties($properties, fn($property) => $property->hasDataSource());

        $this->hydrateLoadableProperty(
            $object,
            $normalizedData,
            $classMetadata,
            $classMetadata->getProperty($propertyName),
            true,
            $loadableProperties
        );
    }

    public function isPropertyLoaded(object $object, string $propertyName) : bool
    {
        return $this->identityMap->isPropertyLoaded($object, $propertyName);
    }

    public function markPropertyLoaded(object $object, string $propertyName, array $extraData = []) : void
    {
        $this->identityMap->markPropertyLoaded($object, $propertyName, $extraData);
    }

    public function hydrate(
        object $object,
        iterable $normalizedData = [],
        ?ClassMetadata\Model\Property\Leaf $parentPropertyMetadata = null
    ) : void {
        $classMetadata = $this->modelLoader->load(get_class($object));
        $this->methodInvoker->invokeVisitorsCallbacks($classMetadata->getCallbacksUsingMetadata()->getBeforeCollection(), $classMetadata);

        $this->hydrateBasicProperties($object, $normalizedData, $classMetadata, $parentPropertyMetadata);
        $this->hydrateLoadableProperties($object, $normalizedData, $classMetadata, false, $parentPropertyMetadata);

        $this->methodInvoker->invokeVisitorsCallbacks($classMetadata->getCallbacksUsingMetadata()->getAfterCollection(), $classMetadata);
    }

    private function hydrateBasicProperties(
        object $object,
        iterable $normalizedData,
        ClassMetadata\Model\Class_ $classMetadata,
        ?ClassMetadata\Model\Property\Leaf $parentPropertyMetadata = null
    ) : void {
        $propertiesMetadata = $this->resolveProperties($classMetadata->getProperties(), $object, $normalizedData, $classMetadata);
        $basicPropertiesMetadata = $this->filterProperties($propertiesMetadata, fn($propertyMetadata) => !$propertyMetadata->hasDataSource());

        foreach ($basicPropertiesMetadata as $propertyMetadata) {
            $this->hydrateBasicProperty($object, $normalizedData, $classMetadata, $propertyMetadata, $parentPropertyMetadata);
        }
    }

    private function hydrateBasicProperty(
        object $object,
        iterable $normalizedData,
        ClassMetadata\Model\Class_ $classMetadata,
        ClassMetadata\Model\Property\Leaf $propertyMetadata,
        ?ClassMetadata\Model\Property\Leaf $parentPropertyMetadata = null
    ) : void {
        $this->initExpressionContext($object, $normalizedData, $classMetadata, $propertyMetadata);
        $this->resolvePropertyDynamicAttributes($propertyMetadata);

        $this->methodInvoker->invokeVisitorsCallbacks($propertyMetadata->getCallbacksUsingMetadata()->getBeforeCollection(), $propertyMetadata);

        if ($propertyMetadata->hasDefaultValue()) {
            //@todo: here middlewares before using a default value (can modify it)
            $propertyModelValue = $this->resolveValue($propertyMetadata->getDefaultValue(), $object);
            $this->setModelValueToProperty($propertyModelValue, $object, $propertyMetadata, $classMetadata, $parentPropertyMetadata);
        } else {
            $event = $this->methodInvoker->invokeVisitorsCallbacks($propertyMetadata->getCallbacksHydration()->getBeforeCollection(), new Event\BeforeHydration($normalizedData));
            $normalizedData = $event->getNormalizedValue();

            //Extract normalized property value from normalized data set
            $propertyNormValue = $this->getPropertyNormalizedValue($normalizedData, $propertyMetadata);
            if (null === $propertyNormValue) {
                return;
            }

            //Hydrate model value from normalized value
            $this->hydrateProperty($propertyModelValue, $propertyNormValue, $object, $propertyMetadata, $parentPropertyMetadata);

            $propertyModelValue = $this->resolveDefinitiveModel($propertyModelValue, $classMetadata, $propertyMetadata, $parentPropertyMetadata);

            //Set model value to property
            if ($propertyMetadata->isCollection()) {
                $this->setCollectionModelValueToProperty($propertyModelValue, $object, $propertyMetadata, $classMetadata, $parentPropertyMetadata);
            } else {
                $this->setModelValueToProperty($propertyModelValue, $object, $propertyMetadata, $classMetadata, $parentPropertyMetadata);
            }

            $event = $this->methodInvoker->invokeVisitorsCallbacks($propertyMetadata->getCallbacksHydration()->getAfterCollection(), new Event\AfterHydration($object, $normalizedData));
        }

        $this->methodInvoker->invokeVisitorsCallbacks($propertyMetadata->getCallbacksUsingMetadata()->getAfterCollection(), $propertyMetadata);

        $this->resetExpressionContext($object, $normalizedData, $classMetadata, $propertyMetadata);
    }

    private function resolveDefinitiveModel(
        $modelValue,
        ClassMetadata\Model\Class_  $classMetadata,
        ClassMetadata\Model\Property\Leaf $propertyMetadata,
        ?ClassMetadata\Model\Property\Leaf $parentPropertyMetadata = null
    ) {
        if (!$propertyMetadata->isObject()
            || !$propertyMetadata->hasInstanceCreation()
            || !$propertyMetadata->getInstanceCreation()->getSetPropertiesThroughCreationMethodWhenPossible()) {
            return $modelValue;
        }

        $instanceCreation = $propertyMetadata->getInstanceCreation();

        $propertyClassMetadata = $this->modelLoader->load($propertyMetadata->getClass());
        $propertyReflectionClass = $propertyClassMetadata->getReflectionClass();
        $constructorMethod = $instanceCreation->hasFactoryMethodName() ? $instanceCreation->getFactoryMethodName() : '__construct';

        if (!$propertyReflectionClass->hasMethod($constructorMethod)
            || ($reflectionMethod = $propertyReflectionClass->getMethod($constructorMethod))->getNumberOfParameters() < 1) {
            return $modelValue;
        }

        $callParameters = [];
        foreach ($reflectionMethod->getParameters() as $parameter) {
            if (!$propertyReflectionClass->hasProperty($parameter->name)) {
                continue;
            }

            $reflectionProperty = $propertyReflectionClass->getProperty($parameter->name);
            $reflectionProperty->setAccessible(true);

            $callParameters[$parameter->name] = $reflectionProperty->getValue($modelValue);
        }

        $oldModelValue = $modelValue;
        if ($instanceCreation->hasFactoryMethodName()) {
            $class = $propertyMetadata->getClass();
            $methodName = $instanceCreation->getFactoryMethodName();
            $modelValue = $class::$methodName(...array_values($callParameters));
        } elseif ($instanceCreation->hasFactoryMethod()) {
            $modelValue = $this->methodInvoker->invokeMethod($instanceCreation->getFactoryMethod(), array_values($callParameters));
        } else {
            $class = $propertyMetadata->getClass();
            $modelValue = new $class(...array_values($callParameters));
        }

        /*if ($reflectionMethod->isConstructor()) {
            $class = $propertyMetadata->getClass();
            $modelValue = new $class(...array_values($callParameters));
        } else {
            $class = $propertyMetadata->getClass();
            $methodName = $instanceCreation->getFactoryMethodName();
            $modelValue = $class::$methodName(...array_values($callParameters));
        }*/

        foreach ($propertyReflectionClass->getProperties() as $propertyName => $void) {

            if (isset($callParameters[$propertyName])) {
                continue;
            }

            $propertyReflProp = $propertyReflectionClass->getProperty($propertyName);
            $propertyReflProp->setAccessible(true);
            $propertyModelValue = $propertyReflProp->getValue($oldModelValue);

            $propMetadata = $propertyClassMetadata->getProperty($propertyName);

            //Set model value to property
            if ($propMetadata->isCollection()) {
                $this->setCollectionModelValueToProperty($propertyModelValue, $modelValue, $propMetadata, $propertyClassMetadata, null);
            } else {
                $this->setModelValueToProperty($propertyModelValue, $modelValue, $propMetadata, $propertyClassMetadata, null);
            }
        }

        return $modelValue;
    }

    private function hydrateLoadableProperties(
        object $object,
        iterable $normalizedData,
        ClassMetadata\Model\Class_ $classMetadata,
        bool $triggerLazyLoading,
        ?ClassMetadata\Model\Property\Leaf $parentPropertyMetadata = null
    ) : void {
        if ($triggerLazyLoading) {
            $this->objectLoadabilityChecker->checkIfIsLoadable($object);
        } else {
            if (! $this->objectLoadabilityChecker->isLoadable($object)) {
                return;
            }
        }

        $properties = $this->resolveProperties($classMetadata->getProperties(), $object, $normalizedData, $classMetadata);
        $loadableProperties = $this->filterProperties($properties, fn($property) => $property->hasDataSource());

        foreach ($loadableProperties as $property) {
            $this->hydrateLoadableProperty(
                $object,
                $normalizedData,
                $classMetadata,
                $property,
                $triggerLazyLoading,
                $loadableProperties,
                $parentPropertyMetadata
            );
        }
    }

    private function hydrateLoadableProperty(
        object $object,
        iterable $normalizedData,
        ClassMetadata\Model\Class_ $classMetadata,
        ClassMetadata\Model\Property\Leaf $propertyMetadata,
        bool $triggerLazyLoading,
        array $allLoadableProperties,
        ?ClassMetadata\Model\Property\Leaf $parentPropertyMetadata = null
    ) : void {
        if ($triggerLazyLoading) {
            $this->objectLoadabilityChecker->checkIfIsLoadable($object);
        }

        $this->initExpressionContext($object, $normalizedData, $classMetadata, $propertyMetadata);

        if (! $triggerLazyLoading && $propertyMetadata->getDataSource()->mustBeLazyLoaded()/* || ! $this->objectLoadabilityChecker->isLoadable($object)*/) {
            return;
        }

        if ($this->identityMap->isPropertyLoaded($object, $propertyMetadata->getName())) {
            return;
        }

        $event = $this->methodInvoker->invokeVisitorsCallbacks($propertyMetadata->getCallbacksUsingMetadata()->getBeforeCollection(), $propertyMetadata);

        //Fetch normalized data set from property data source - because we do not use the basic data set of $object.
        $dataSourceNormalizedData = $this->dataFetcher->fetchDataSetByProperty(
            $propertyMetadata,
            $object,
            $classMetadata
        );

        $this->resetExpressionContext($object, $normalizedData, $classMetadata, $propertyMetadata);
        //$dataSourceNormalizedData = $event->getNormalizedValue();

        $propertiesIndexedByDataSources = $this->extractPropertiesWithGivenPropertyDataSource($propertyMetadata, $allLoadableProperties);
        foreach ($propertiesIndexedByDataSources as $propertyMetadata) {
            $this->initExpressionContext($object, $normalizedData, $classMetadata, $propertyMetadata);

            //Extract normalized property value from normalized data set
            $propertyNormValue = $this->getPropertyNormalizedValue($dataSourceNormalizedData, $propertyMetadata);
            if (null === $propertyNormValue) {
                continue;
            }

            //Hydrate model value from normalized value
            $event = $this->methodInvoker->invokeVisitorsCallbacks($propertyMetadata->getCallbacksHydration()->getBeforeCollection(), new Event\BeforeHydration($dataSourceNormalizedData));
            $this->hydrateProperty($propertyModelValue, $propertyNormValue, $object, $propertyMetadata, null, $parentPropertyMetadata);
            $this->methodInvoker->invokeVisitorsCallbacks($propertyMetadata->getCallbacksHydration()->getAfterCollection(), new Event\AfterHydration($object, $dataSourceNormalizedData));

            $propertyModelValue = $this->resolveDefinitiveModel($propertyModelValue, $classMetadata, $propertyMetadata, $parentPropertyMetadata);

            //Set model value to property
            if ($propertyMetadata->isCollection()) {
                $this->setCollectionModelValueToProperty($propertyModelValue, $object, $propertyMetadata, $classMetadata);
            } else {
                $this->setModelValueToProperty($propertyModelValue, $object, $propertyMetadata, $classMetadata);
            }

            $extraData = [];
            if (isset($previousExpressionContext['object'])) {
                $extraData['parent_object'] = $previousExpressionContext['object'];
            }
            $this->identityMap->markPropertyLoaded($object, $propertyMetadata->getName(), $extraData);

            $this->resetExpressionContext($object, $normalizedData, $classMetadata, $propertyMetadata);
        }
    }

    private function getPropertyNormalizedValue($normalizedData, ClassMetadata\Model\Property\Leaf $property)
    {
        if ($property->hasRawDataLocation() && Enum\RawDataLocation::PARENT_ === $property->getRawDataLocation()) {
            return $normalizedData;
        }

        $keyInNormalizedData = $property->getKeyInRawData();
        if (! isset($normalizedData[$keyInNormalizedData])) {//@todo: log missing keys or throw an exception.
            return null;
        }

        return $normalizedData[$keyInNormalizedData];
    }

    private function hydrateProperty(
        &$modelValue,
        $normalizedValue,
        object $objectToSet,
        ClassMetadata\Model\Property\Leaf $property,
        ?ClassMetadata\Model\Property\Leaf $parentPropertyMetadata = null
    ) : void {
        /*if (! $property->areRawDataToHydrate()) {
            $modelValue = $normalizedValue;
            return;
        }*/

        if ($property->isCollection()) {
            $this->hydrateCollectionProperty($modelValue, $normalizedValue, $objectToSet, $property, $parentPropertyMetadata);
        } else {
            if ($property->isObject()) {
                $this->hydrateObjectProperty($modelValue, $normalizedValue, $objectToSet, $property, null, $parentPropertyMetadata);
            } else {
                $this->hydrateScalarProperty($modelValue, $normalizedValue, $property);
            }
        }
    }

    private function hydrateCollectionProperty(
        &$modelValue,
        $normalizedValue,
        object $objectToSet,
        ClassMetadata\Model\Property\Leaf $property,
        ?ClassMetadata\Model\Property\Leaf $parentPropertyMetadata = null
    ) : void {
        $modelValue = [];


        foreach ($normalizedValue as $normalizedValueItemKey => $normalizedValueItem) {
            $this->expressionContext['normalized_item_data'] = $normalizedValueItem;

            $currentItemCollectionClassInfo = $property->getCurrentItemCollectionClass($this->expressionEvaluator, $this->methodInvoker);
            if ($currentItemCollectionClassInfo->hasRawDataLocation()) {
                $normalizedValue = $currentItemCollectionClassInfo->locateConcernedRawData(
                    $normalizedValue,
                    $this->methodInvoker,
                    $objectToSet
                );
            }

            if ($currentItemCollectionClassInfo->isObject()) {
                //$this->expressionContext['normalized_item_data'] = $normalizedValueItem;

                $this->hydrateObjectProperty(
                    $modelValueItem,
                    $normalizedValueItem,
                    $objectToSet,
                    $property,
                    $currentItemCollectionClassInfo,
                    $parentPropertyMetadata
                );
                $modelValue[$normalizedValueItemKey] = $modelValueItem;
            } else {
                $this->hydrateScalarProperty($modelValueItem, $normalizedValueItem, $property);
                $modelValue[$normalizedValueItemKey] = $modelValueItem;
            }

            unset($this->expressionContext['normalized_item_data']);
        }
    }

    private function hydrateObjectProperty(
        &$modelValue,
        $normalizedValue,
        object $objectToSet,
        ClassMetadata\Model\Property\Leaf $propertyMetadata,
        ?ClassMetadata\Dto\ClassInfo $currentItemCollectionClassInfo,
        ?ClassMetadata\Model\Property\Leaf $parentPropertyMetadata = null
    ) : void {
        if ($propertyMetadata->hasRawDataLocation()) {
            $normalizedValue = $propertyMetadata->locateConcernedRawData(
                $normalizedValue,
                $this->methodInvoker,
                $objectToSet
            );
        }

        $event = $this->methodInvoker->invokeVisitorsCallbacks($propertyMetadata->getCallbacksHydration()->getBeforeCollection(), new Event\BeforeHydration($normalizedValue));
        $normalizedValue = $event->getNormalizedValue();

        $propertyClass = $propertyMetadata->isCollection() ? $currentItemCollectionClassInfo->getClass() : $propertyMetadata->getClass();
        $instanceCreation = $propertyMetadata->getInstanceCreation();

        /**
         * If constructor method args are to hydrate, we create a temporary object.
         * Definitive object will be construct by passing to constructor method some hydrated properties values.
         * See method Hydrator::resolveDefinitiveModel().
         */
        if ($instanceCreation
            && $instanceCreation->getSetPropertiesThroughCreationMethodWhenPossible()) {
            $propertyClassMetadata = $this->modelLoader->load($propertyMetadata->getClass());
            $modelValue = $propertyClassMetadata->getReflectionClass()->getNativeReflectionClass()->newInstanceWithoutConstructor();
        } else {
            if ($instanceCreation && $instanceCreation->hasFactoryMethodName()) {
                $modelValue = $propertyClass::{$instanceCreation->getFactoryMethodName()}();
            } else {
                $modelValue = new $propertyClass;
            }
        }

        $this->hydrate($modelValue, $normalizedValue, $propertyMetadata);

        $event = $this->methodInvoker->invokeVisitorsCallbacks($propertyMetadata->getCallbacksHydration()->getAfterCollection(), new Event\AfterHydration($modelValue, $normalizedValue));
        $modelValue = $event->getModelValue();
    }

    private function hydrateScalarProperty(&$modelValue, $normalizedValue, ClassMetadata\Model\Property\Leaf $property) : void
    {
        $event = $this->methodInvoker->invokeVisitorsCallbacks($property->getCallbacksHydration()->getBeforeCollection(), new Event\BeforeHydration($normalizedValue));
        $normalizedValue = $event->getNormalizedValue();


        $modelValue = $normalizedValue;

        $event = $this->methodInvoker->invokeVisitorsCallbacks($property->getCallbacksHydration()->getAfterCollection(), new Event\AfterHydration($modelValue, $normalizedValue));
        $modelValue = $event->getModelValue();
    }

    private function setModelValueToProperty(
        $valueToBeSetted,
        object $objectToSet,
        ClassMetadata\Model\Property\Leaf $propToSetMetadata,
        ClassMetadata\Model\Class_ $classMetadata,
        ?ClassMetadata\Model\Property\Leaf $parentPropertyMetadata = null
    ) : void {
        /**
         * If constructor method args are to hydrate, we create a temporary object
         * and we don't use it's accessors, we access directly properties
         * because we want this to be "invisible".
         * Definitive object will be construct by passing to constructor as args
         * some hydrated properties values depending on constructor prototype
         * and hydrating remaining attributes via accessors.
         * See method Hydrator::resolveDefinitiveModel().
         */
        if (/*$classMetadata->areAccessorsToBypass() ||*/ null !== $parentPropertyMetadata
            && $parentPropertyMetadata->hasInstanceCreation()
            && $parentPropertyMetadata->getInstanceCreation()->getSetPropertiesThroughCreationMethodWhenPossible()) {
            $memberAccessStrategy = $this->memberAccessStrategyFactory->property($objectToSet, $classMetadata);
            $memberAccessStrategy->setValue($valueToBeSetted, $propToSetMetadata);
            return;
        }

        $this->methodInvoker->invokeVisitorsCallbacks(
            $propToSetMetadata->getCallbacksAssigningHydratedValue()->getBeforeCollection(),
            new Event\BeforeSettingHydratedValue($valueToBeSetted, $objectToSet, $propToSetMetadata->getName())
        );

        $memberAccessStrategy = $this->memberAccessStrategyFactory->getterSetter($objectToSet, $classMetadata);
        $memberAccessStrategy->setValue($valueToBeSetted, $propToSetMetadata);

        $this->methodInvoker->invokeVisitorsCallbacks($propToSetMetadata->getCallbacksAssigningHydratedValue()->getAfterCollection());
    }

    private function setCollectionModelValueToProperty(
        array $valueToBeSetted,
        object $objectToSet,
        ClassMetadata\Model\Property\Leaf $propToSetMetadata,
        ClassMetadata\Model\Class_ $classMetadata,
        ?ClassMetadata\Model\Property\Leaf $parentPropertyMetadata = null
    ) : void {
        $this->methodInvoker->invokeVisitorsCallbacks(
            $propToSetMetadata->getCallbacksAssigningHydratedValue()->getBeforeCollection(),
            new Event\BeforeSettingHydratedValue($valueToBeSetted, $objectToSet, $propToSetMetadata->getName())
        );

        if (/*$classMetadata->areAccessorsToBypass() ||*/ null !== $parentPropertyMetadata
            && $parentPropertyMetadata->hasInstanceCreation()
            && $parentPropertyMetadata->getInstanceCreation()->getSetPropertiesThroughCreationMethodWhenPossible()
        ) {
            $memberAccessStrategy = $this->memberAccessStrategyFactory->property($objectToSet, $classMetadata);
        } else {
            $memberAccessStrategy = $this->memberAccessStrategyFactory->getterSetter($objectToSet, $classMetadata);
        }

        $memberAccessStrategy->setValues($valueToBeSetted, $propToSetMetadata);

        $this->methodInvoker->invokeVisitorsCallbacks($propToSetMetadata->getCallbacksAssigningHydratedValue()->getAfterCollection());
    }

    /*public function guessMemberAccessStrategyFromLoadingOption(object $object, ClassMetadata\Model\Class_ $classMetadata, bool $byPassLoading) : MemberAccessStrategyInterface
    {
        if ($byPassLoading) {
            return $this->createPropertyAccessStrategy($object, $classMetadata);
        }

        return $this->createGetterSetterAccessStrategy($object, $classMetadata);
    }*/

    private function filterProperties(array $properties, $predicateFunction) : array
    {
        $filteredProperties = [];

        foreach ($properties as $property) {
            if ($predicateFunction($property)) {
                $filteredProperties[] = $property;
            }
        }

        return $filteredProperties;
    }

    private function extractPropertiesWithGivenPropertyDataSource(ClassMetadata\Model\Property\Leaf $propertyWithDataSourceToFilterOn, array $properties)
    {
        $dataSourceToFilterOn = $propertyWithDataSourceToFilterOn->getDataSource();

        if (null === ($dataSourceIdToFilterOn = $dataSourceToFilterOn->getId())) {
            return [$propertyWithDataSourceToFilterOn];
        }

        $loadingScope = $dataSourceToFilterOn->getLoadingScope();
        if (Enum\DataSourceLoadingScope::PROPERTY === $loadingScope) {
            return [$propertyWithDataSourceToFilterOn];
        }

        $filteredProperties = [];

        foreach ($properties as $property) {
            if (!$property->hasDataSource()) {
                continue;
            }

            $dataSource = $property->getDataSource();
            if (null !== ($dataSourceId = $dataSource->getId()) && $dataSourceId === $dataSourceIdToFilterOn) {
                $loadingScopeKeys = $dataSource->getLoadingScopeKeys();

                switch ($dataSource->getLoadingScope()) {
                    case Enum\DataSourceLoadingScope::DATA_SOURCE_ONLY_KEYS:
                        if (!isset($loadingScopeKeys[$property->getKeyInRawData()])) {
                            continue 2;
                        }
                        break;
                    case Enum\DataSourceLoadingScope::DATA_SOURCE_EXCEPT_KEYS:
                        if (isset($loadingScopeKeys[$property->getKeyInRawData()])) {
                            continue 2;
                        }
                        break;
                }

                //else defaults to Enum\DataSource::DATA_SOURCE
                $filteredProperties[] = $property;
            }
        }

        return $filteredProperties;
    }


    private function resolveProperties(
        array $properties,
        object $object,
        iterable $normalizedData,
        ClassMetadata\Model\Class_ $classMetadata
    ) : array {
        $resolvedProperties = [];

        foreach ($properties as $property) {
            $resolvedProperties[] = $this->resolveProperty($property, $object, $normalizedData, $classMetadata);
        }

        return $resolvedProperties;
    }

    private function resolveProperty(
        ClassMetadata\Model\Property $property,
        object $object,
        iterable $normalizedData,
        ClassMetadata\Model\Class_ $classMetadata
    ) {
        $resolvedProperty = $property;
        $this->initExpressionContext($object, $normalizedData, $classMetadata, $property);
        if ($property->hasCandidates()) {
            $resolvedProperty = $this->propertyCandidatesResolver->resolveGoodCandidate($property);
        }
        $this->resetExpressionContext($object, $normalizedData, $classMetadata, $property);//Not resolved property !

        $this->initExpressionContext($object, $normalizedData, $classMetadata, $resolvedProperty);
        $this->resolvePropertyDynamicAttributes($resolvedProperty);
        $this->resetExpressionContext($object, $normalizedData, $classMetadata, $resolvedProperty);

        return $resolvedProperty;
    }

    private function resolvePropertyDynamicAttributes(ClassMetadata\Model\Property\Leaf $property) : void
    {
        foreach ($property->getDynamicAttributes() as $dynamicAttributeName => $dynamicAttribute) {
            if ($dynamicAttribute instanceof ClassMetadata\Model\Method) {
                $property->$dynamicAttributeName = $this->methodInvoker->invokeMethod($dynamicAttribute);
            } elseif ($dynamicAttribute instanceof ClassMetadata\Model\Expression) {
                $property->$dynamicAttributeName = $this->expressionEvaluator->resolveAdvancedExpression($dynamicAttribute->getValue());
            } else {
                throw new \LogicException(sprintf(
                    'Cannot resolve dynamic value of attribute "%s::%s" of property %s.' .
                    PHP_EOL . 'Dynamic value must be an instance of either "%s" or "%s" but type "%s" given.',
                    ClassMetadata\Model\Property\Leaf::class,
                    $dynamicAttributeName,
                    $property->getName(),
                    ClassMetadata\Model\Method::class,
                    ClassMetadata\Model\Expression::class,
                    is_object($dynamicAttribute) ? get_class($dynamicAttribute) : gettype($dynamicAttribute)
                ));
            }
        }
    }

    private function initExpressionContext(
        object $object,
        iterable $normalizedData,
        ClassMetadata\Model\Class_ $classMetadata,
        ?ClassMetadata\Model\Property $property
    ) : iterable {
        $previousExpressionContext = $this->expressionContext;

        $this->expressionContext['object'] = $object;
        $this->expressionContext['normalized_data'] = $normalizedData;
        $this->expressionContext['class_metadata'] = $classMetadata;
        $this->expressionContext['provider'] = $this->essentialDataProvider->withContext($object, $classMetadata);

        if ($property && $property->hasVariables()) {
            if (! isset($this->expressionContext['current_variables_stats'])) {
                $this->expressionContext['current_variables_stats'] = [];
            }

            foreach ($property->getVariables() as $variableKey => $variableValue) {
                if (isset($this->expressionContext['current_variables_stats'][$variableKey])) {
                    $this->expressionContext['current_variables_stats'][$variableKey]++;
                } else {
                    $this->expressionContext['current_variables_stats'] = [$variableKey => 0];
                    $this->expressionContext['current_variables'] = [$variableKey => $variableValue];
                }
            }
        }

        return $previousExpressionContext;
    }

    private function resetExpressionContext(
        object $object,
        iterable $normalizedData,
        ClassMetadata\Model\Class_ $classMetadata,
        ClassMetadata\Model\Property $property
    ) : void {
        unset($this->expressionContext['object']);
        unset($this->expressionContext['normalized_data']);
        unset($this->expressionContext['class_metadata']);
        //unset($this->expressionContext['provider']);

        if ($property->hasVariables()) {
            foreach ($property->getVariables() as $variableKey => $variableValue) {
                if (isset($this->expressionContext['current_variables'][$variableKey])) {
                    $this->expressionContext['current_variables_stats'][$variableKey]--;
                }
            }

            foreach ($this->expressionContext['current_variables_stats'] as $variableKey => $occurencesNumber) {
                if (0 === $occurencesNumber) {
                    unset($this->expressionContext['current_variables'][$variableKey]);
                }
            }

            $this->expressionContext['current_variables_stats'] = array_filter(
                $this->expressionContext['current_variables_stats'],
                fn ($occurencesNumber) => $occurencesNumber > 0
            );
        }
    }
}
