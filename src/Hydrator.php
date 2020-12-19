<?php

namespace Big\Hydrator;

use Big\Hydrator\ClassMetadata;

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

        $this->hydrateLoadableProperties($object, [], $this->modelLoader->load($object), true);
    }

    public function loadProperty(object $object, string $propertyName) : void
    {
        $this->objectLoadabilityChecker->checkIfIsLoadable($object);

        $normalizedData = [];
        $classMetadata = $this->modelLoader->load($object);
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

    public function hydrate(object $object, iterable $normalizedData = []) : void
    {
        $classMetadata = $this->modelLoader->load($object);
        $this->methodInvoker->invokeVisitorsCallbacks($classMetadata->getCallbacksUsingMetadata()->getBeforeCollection(), $classMetadata);

        $this->hydrateBasicProperties($object, $normalizedData, $classMetadata, false);
        $this->hydrateLoadableProperties($object, $normalizedData, $classMetadata, false);

        $this->methodInvoker->invokeVisitorsCallbacks($classMetadata->getCallbacksUsingMetadata()->getAfterCollection(), $classMetadata);
    }

    private function hydrateBasicProperties(object $object, iterable $normalizedData, ClassMetadata\Model\Class_ $classMetadata) : void
    {
        $properties = $this->resolveProperties($classMetadata->getProperties(), $object, $normalizedData, $classMetadata);
        $basicProperties = $this->filterProperties($properties, fn($property) => !$property->hasDataSource());

        foreach ($basicProperties as $property) {
            $this->hydrateBasicProperty($object, $normalizedData, $classMetadata, $property);
        }
    }

    private function hydrateBasicProperty(
        object $object,
        iterable $normalizedData,
        ClassMetadata\Model\Class_ $classMetadata,
        ClassMetadata\Model\Property\Leaf $property
    ) : void {

        $this->initExpressionContext($object, $normalizedData, $classMetadata, $property);
        $this->resolvePropertyDynamicAttributes($property);

        $this->methodInvoker->invokeVisitorsCallbacks($property->getCallbacksUsingMetadata()->getBeforeCollection(), $property);

        if ($property->hasDefaultValue()) {
            //@todo: here middlewares before using a default value (can modify it)
            $propertyModelValue = $this->resolveValue($property->getDefaultValue(), $object);
            $this->setModelValueToProperty($propertyModelValue, $object, $property, $classMetadata);
        } else {
            $event = $this->methodInvoker->invokeVisitorsCallbacks($property->getCallbacksHydration()->getBeforeCollection(), new Event\BeforeHydration($normalizedData));
            $normalizedData = $event->getNormalizedValue();

            //Extract normalized property value from normalized data set
            $propertyNormValue = $this->getPropertyNormalizedValue($normalizedData, $property);
            if (null === $propertyNormValue) {
                return;
            }

            //Hydrate model value from normalized value
            $this->hydrateProperty($propertyModelValue, $propertyNormValue, $object, $property);

            //Set model value to property
            if ($property->isCollection()) {
                $this->setCollectionModelValueToProperty($propertyModelValue, $object, $property, $classMetadata);
            } else {
                $this->setModelValueToProperty($propertyModelValue, $object, $property, $classMetadata);
            }

            $event = $this->methodInvoker->invokeVisitorsCallbacks($property->getCallbacksHydration()->getAfterCollection(), new Event\AfterHydration($object, $normalizedData));
        }

        $this->methodInvoker->invokeVisitorsCallbacks($property->getCallbacksUsingMetadata()->getAfterCollection(), $property);

        $this->resetExpressionContext($object, $normalizedData, $classMetadata, $property);
    }

    private function hydrateLoadableProperties(
        object $object,
        iterable $normalizedData,
        ClassMetadata\Model\Class_ $classMetadata,
        bool $triggerLazyLoading
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
            $this->hydrateLoadableProperty($object, $normalizedData, $classMetadata, $property, $triggerLazyLoading, $loadableProperties);
        }
    }

    private function hydrateLoadableProperty(
        object $object,
        iterable $normalizedData,
        ClassMetadata\Model\Class_ $classMetadata,
        ClassMetadata\Model\Property\Leaf $property,
        bool $triggerLazyLoading,
        array $allLoadableProperties
    ) : void {
        if ($triggerLazyLoading) {
            $this->objectLoadabilityChecker->checkIfIsLoadable($object);
        }

        $this->initExpressionContext($object, $normalizedData, $classMetadata, $property);

        if (! $triggerLazyLoading && $property->getDataSource()->mustBeLazyLoaded()/* || ! $this->objectLoadabilityChecker->isLoadable($object)*/) {
            return;
        }

        if ($this->identityMap->isPropertyLoaded($object, $property->getName())) {
            return;
        }

        $event = $this->methodInvoker->invokeVisitorsCallbacks($property->getCallbacksUsingMetadata()->getBeforeCollection(), $property);

        //Fetch normalized data set from property data source - because we do not use the basic data set of $object.
        $dataSourceNormalizedData = $this->dataFetcher->fetchDataSetByProperty(
            $property,
            $object,
            $classMetadata
        );

        $this->resetExpressionContext($object, $normalizedData, $classMetadata, $property);
        //$dataSourceNormalizedData = $event->getNormalizedValue();

        $propertiesIndexedByDataSources = $this->extractPropertiesWithGivenPropertyDataSource($property, $allLoadableProperties);
        foreach ($propertiesIndexedByDataSources as $property) {
            $this->initExpressionContext($object, $normalizedData, $classMetadata, $property);

            //Extract normalized property value from normalized data set
            $propertyNormValue = $this->getPropertyNormalizedValue($dataSourceNormalizedData, $property);
            if (null === $propertyNormValue) {
                return;
            }

            //Hydrate model value from normalized value
            $event = $this->methodInvoker->invokeVisitorsCallbacks($property->getCallbacksHydration()->getBeforeCollection(), new Event\BeforeHydration($dataSourceNormalizedData));
            $this->hydrateProperty($propertyModelValue, $propertyNormValue, $object, $property);
            $this->methodInvoker->invokeVisitorsCallbacks($property->getCallbacksHydration()->getAfterCollection(), new Event\AfterHydration($object, $dataSourceNormalizedData));

            //Set model value to property
            if ($property->isCollection()) {
                $this->setCollectionModelValueToProperty($propertyModelValue, $object, $property, $classMetadata);
            } else {
                $this->setModelValueToProperty($propertyModelValue, $object, $property, $classMetadata);
            }

            $extraData = [];
            if (isset($previousExpressionContext['object'])) {
                $extraData['parent_object'] = $previousExpressionContext['object'];
            }
            $this->identityMap->markPropertyLoaded($object, $property->getName(), $extraData);

            $this->resetExpressionContext($object, $normalizedData, $classMetadata, $property);
        }
    }

    private function getPropertyNormalizedValue($normalizedData, ClassMetadata\Model\Property\Leaf $property)
    {
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
        ClassMetadata\Model\Property\Leaf $property
    ) : void {
        /*if (! $property->areRawDataToHydrate()) {
            $modelValue = $normalizedValue;
            return;
        }*/

        if ($property->isCollection()) {
            $modelValue = [];

            foreach ($normalizedValue as $normalizedValueItemKey => $normalizedValueItem) {
                $this->expressionContext['normalized_item_data'] = $normalizedValueItem;
                $currentItemCollectionClass = $property->getCurrentItemCollectionClass($this->expressionEvaluator, $this->methodInvoker);

                if (null !== $currentItemCollectionClass) {
                    $this->hydrateObjectModel($modelValueItem, $normalizedValueItem, $property, $currentItemCollectionClass);
                    $modelValue[$normalizedValueItemKey] = $modelValueItem;
                } else {
                    $this->hydrateScalarModel($modelValueItem, $normalizedValueItem, $property);
                    $modelValue[$normalizedValueItemKey] = $modelValueItem;
                }

                unset($this->expressionContext['normalized_item_data']);
            }
        } else {
            if ($property->isObject()) {
                $this->hydrateObjectModel($modelValue, $normalizedValue, $property);
            } else {
                $this->hydrateScalarModel($modelValue, $normalizedValue, $property);
            }
        }
    }

    private function hydrateObjectModel(
        &$modelValue,
        $normalizedValue,
        ClassMetadata\Model\Property\Leaf $property,
        ?string $currentItemCollectionClass = null
    ) : void {
        $event = $this->methodInvoker->invokeVisitorsCallbacks($property->getCallbacksHydration()->getBeforeCollection(), new Event\BeforeHydration($normalizedValue));
        $normalizedValue = $event->getNormalizedValue();

        $propertyClass = $property->isCollection() ? $currentItemCollectionClass : $property->getClass();
        $modelValue =  new $propertyClass;

        $this->hydrate($modelValue, $normalizedValue);

        $event = $this->methodInvoker->invokeVisitorsCallbacks($property->getCallbacksHydration()->getAfterCollection(), new Event\AfterHydration($modelValue, $normalizedValue));
        $modelValue = $event->getModelValue();
    }

    private function hydrateScalarModel(&$modelValue, $normalizedValue, ClassMetadata\Model\Property\Leaf $property) : void
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
        ClassMetadata\Model\Property\Leaf $propToSet,
        ClassMetadata\Model\Class_ $classMetadata
    ) : void {
        $this->methodInvoker->invokeVisitorsCallbacks(
            $propToSet->getCallbacksAssigningHydratedValue()->getBeforeCollection(),
            new Event\BeforeSettingHydratedValue($valueToBeSetted, $objectToSet, $propToSet->getName())
        );

        $this->memberAccessStrategyFactory->getterSetter($objectToSet, $classMetadata)->setValue($valueToBeSetted, $propToSet);

        $this->methodInvoker->invokeVisitorsCallbacks($propToSet->getCallbacksAssigningHydratedValue()->getAfterCollection());
    }

    private function setCollectionModelValueToProperty(
        array $valueToBeSetted,
        object $objectToSet,
        ClassMetadata\Model\Property\Leaf $propToSet,
        ClassMetadata\Model\Class_ $classMetadata
    ) : void {
        $this->methodInvoker->invokeVisitorsCallbacks(
            $propToSet->getCallbacksAssigningHydratedValue()->getBeforeCollection(),
            new Event\BeforeSettingHydratedValue($valueToBeSetted, $objectToSet, $propToSet->getName())
        );

        $this->memberAccessStrategyFactory->getterSetter($objectToSet, $classMetadata)->setValues($valueToBeSetted, $propToSet);

        $this->methodInvoker->invokeVisitorsCallbacks($propToSet->getCallbacksAssigningHydratedValue()->getAfterCollection());
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
        if (ClassMetadata\Model\DataSource::LOADING_SCOPE_PROPERTY === $loadingScope) {
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
                    case ClassMetadata\Model\DataSource::LOADING_SCOPE_DATA_SOURCE_ONLY_KEYS:
                        if (!isset($loadingScopeKeys[$property->getName()])) {
                            continue 2;
                        }
                        break;
                    case ClassMetadata\Model\DataSource::LOADING_SCOPE_DATA_SOURCE_EXCEPT_KEYS:
                        if (isset($loadingScopeKeys[$property->getName()])) {
                            continue 2;
                        }
                        break;
                }

                //else defaults to ClassMetadata\Model\DataSource::LOADING_SCOPE_DATA_SOURCE
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
        ClassMetadata\Model\Property\Leaf $property,
        object $object,
        iterable $normalizedData,
        ClassMetadata\Model\Class_ $classMetadata
    ) {
        $this->initExpressionContext($object, $normalizedData, $classMetadata, $property);
        if ($property->hasCandidates()) {
            $property = $this->propertyCandidatesResolver->resolveGoodCandidate($property);
        }
        $this->resetExpressionContext($object, $normalizedData, $classMetadata, $property);

        $this->initExpressionContext($object, $normalizedData, $classMetadata, $property);
        $this->resolvePropertyDynamicAttributes($property);
        $this->resetExpressionContext($object, $normalizedData, $classMetadata, $property);

        return $property;
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
        ?ClassMetadata\Model\Property\Leaf $property
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
                    $this->expressionContext['current_variables'][$variableKey] = $variableValue;
                }
            }
        }

        return $previousExpressionContext;
    }

    private function resetExpressionContext(
        object $object,
        iterable $normalizedData,
        ClassMetadata\Model\Class_ $classMetadata,
        ClassMetadata\Model\Property\Leaf $property
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
