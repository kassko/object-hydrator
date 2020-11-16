<?php

namespace Big\Hydrator;

use function array_filter;

class Hydrator
{
    use CandidatePropertiesResolverAwareTrait;

    private ClassMetadataLoader $classMetadataLoader;
    private MemberAccessStrategyFactory $memberAccessStrategyFactory;
    private IdentityMap $identityMap;
    private ObjectLoadabilityChecker $objectLoadabilityChecker;
    private DataFetcher $dataFetcher;
    private MethodInvoker $invoker;
    private ExpressionContext $expressionContext;
    private EssentialDataProvider $essentialDataProvider;
    private Config $config;

    public function __construct(
        ClassMetadataLoader $classMetadataLoader,
        MemberAccessStrategyFactory $memberAccessStrategyFactory,
        IdentityMap $identityMap,
        ObjectLoadabilityChecker $objectLoadabilityChecker,
        DataFetcher $dataFetcher,
        MethodInvoker $methodInvoker,
        ExpressionContext $expressionContext,
        EssentialDataProvider $essentialDataProvider,
        Config $config
    ) {
        $this->classMetadataLoader = $classMetadataLoader;
        $this->memberAccessStrategyFactory = $memberAccessStrategyFactory;
        $this->identityMap = $identityMap;
        $this->objectLoadabilityChecker = $objectLoadabilityChecker;
        $this->dataFetcher = $dataFetcher;
        $this->methodInvoker = $methodInvoker;
        $this->expressionContext = $expressionContext;
        $this->essentialDataProvider = $essentialDataProvider;
        $this->config = $config;
    }

    public function load(object $object) : void
    {
        if (! $this->objectLoadabilityChecker->checkIfIsLoadable($object)) {
            return;
        }

        $this->hydrateLoadableProperties($object, [], $this->classMetadataLoader->loadMetadata($object), true);
    }

    public function loadProperty(object $object, string $propertyName) : void
    {
        if (! $this->objectLoadabilityChecker->checkIfIsLoadable($object)) {
            return;
        }

        $this->hydrateLoadableProperty(
            $object,
            $normalizedData,
            $classMetadata = $this->classMetadataLoader->loadMetadata($object),
            $classMetadata->getCandidateProperties($propertyName),
            true
        );
    }

    public function hydrate(object $object, iterable $normalizedData = []) : void
    {
        $classMetadata = $this->classMetadataLoader->loadMetadata($object);
        $this->methodInvoker->invokeVisitorsCallbacks($classMetadata->getBeforeUsingLoadedMetadata(), $classMetadata);

        $this->hydrateOriginalProperties($object, $normalizedData, $classMetadata);
        $this->hydrateLoadableProperties($object, $normalizedData, $classMetadata);

        $this->methodInvoker->invokeVisitorsCallbacks($classMetadata->getAfterUsingLoadedMetadata(), $classMetadata);
    }

    private function hydrateOriginalProperties(object $object, iterable $normalizedData, ClassMetadata $classMetadata) : void
    {
        foreach ($classMetadata->getBasicCandidatesProperties() as $candidateProperties) {
            $previousExpressionContext = $this->initExpressionContext($object, $normalizedData, $classMetadata, null);

            $property = $this->resolveManagedProperty($candidateProperties->getName(), $classMetadata);

            $this->methodInvoker->invokeVisitorsCallbacks($property->getBeforeUsingLoadedMetadata(), $property);

            if ($property->hasDefaultValue()) {
                //@todo: here middlewares before using a default value (can modify it)
                $propertyModelValue = $this->resolveValue($property->getDefaultValue(), $object);
                $this->setModelValueToProperty($propertyModelValue, $object, $property, $classMetadata);
            } else {
                $event = $this->methodInvoker->invokeVisitorsCallbacks($classMetadata->getBeforeHydration(), new Event\BeforeHydration($normalizedData));
                $normalizedData = $event->getNormalizedValue();

               //Extract normalized property value from normalized data set
                $propertyNormValue = $this->getPropertyNormalizedValue($normalizedData, $property);
                if (null === $propertyNormValue) {
                    continue;
                }

                //Hydrate model value from normalized value
                $this->hydrateProperty($propertyModelValue, $propertyNormValue, $object, $property);

                //Set model value to property
                if ($property->isCollection()) {
                    $this->setCollectionModelValueToProperty($propertyModelValue, $object, $property, $classMetadata);
                } else {
                    $this->setModelValueToProperty($propertyModelValue, $object, $property, $classMetadata);
                }

                $event = $this->methodInvoker->invokeVisitorCallback($classMetadata->getAfterHydration(), new Event\AfterHydration($object, $normalizedData));
            }

            $this->methodInvoker->invokeVisitorsCallbacks($property->getAfterUsingLoadedMetadata(), $property);
        }
    }

    private function hydrateLoadableProperties(
        object $object,
        iterable $normalizedData,
        ClassMetadata $classMetadata,
        bool $enforceLoading = false
    ) : void {
        if ($enforceLoading) {
            $this->objectLoadabilityChecker->checkIfIsLoadable($object);
        } else {
            if (! $this->objectLoadabilityChecker->isLoadable($object)) {
                return;
            }
        }

        foreach ($classMetadata->getLoadableCandidatesProperties() as $candidateProperties) {
            $property = $this->resolveManagedProperty($candidateProperties->getName(), $classMetadata);

            $this->hydrateLoadableProperty($object, $normalizedData, $classMetadata, $property, $enforceLoading);
        }
    }

    private function hydrateLoadableProperty(
        object $object,
        iterable $normalizedData,
        ClassMetadata $classMetadata,
        ClassMetadata\Property $property,
        bool $enforceLoading = false
    ) : void {
        if ($enforceLoading) {
            $this->objectLoadabilityChecker->checkIfIsLoadable($object);
        } else {
            //var_dump(__FUNCTION__, $property->getName(), $property->mustBeLazyLoaded());
            if ($property->mustBeLazyLoaded()/* || ! $this->objectLoadabilityChecker->isLoadable($object)*/) {
                return;
            }
        }

        if ($this->identityMap->isPropertyLoaded($object, $property->getName())) {
            return;
        }

        $this->methodInvoker->invokeVisitorsCallbacks($property->getBeforeUsingLoadedMetadata(), $property);

        $previousExpressionContext = $this->initExpressionContext($object, $normalizedData, $classMetadata, $property);

        //Fetch normalized data set from property data source - because we do not use the basic data set of $object.
        $dataSourceNormalizedData = $this->dataFetcher->fetchDataSetByProperty(
            $property,
            $object,
            $classMetadata
        );

        //$this->resetExpressionContext($object, $normalizedData, $classMetadata, $property);

        $event = $this->methodInvoker->invokeVisitorsCallbacks($classMetadata->getBeforeHydration(), new Event\BeforeHydration($dataSourceNormalizedData));
        $dataSourceNormalizedData = $event->getNormalizedValue();

        //Extract normalized property value from normalized data set
        $propertyNormValue = $this->getPropertyNormalizedValue($dataSourceNormalizedData, $property);
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

        $this->methodInvoker->invokeVisitorCallback($classMetadata->getAfterHydration(), new Event\AfterHydration($object, $dataSourceNormalizedData));

        $extraData = [];
        if (isset($previousExpressionContext['object'])) {
            $extraData['parent_object'] = $previousExpressionContext['object'];
        }

        $this->identityMap->markPropertyLoaded($object, $property->getName(), $extraData);
    }

    public function isPropertyLoaded(object $object, string $propertyName) : bool
    {
        return $this->identityMap->isPropertyLoaded($object, $propertyName);
    }

    public function markPropertyLoaded(object $object, string $propertyName, array $extraData = []) : void
    {
        $this->identityMap->markPropertyLoaded($object, $propertyName, $extraData);
    }

    private function initExpressionContext(
        object $object,
        iterable $normalizedData,
        ClassMetadata $classMetadata,
        ?ClassMetadata\Property $property
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
        ClassMetadata $classMetadata,
        ClassMetadata\Property $property
    ) : void {
        unset($this->expressionContext['object']);
        unset($this->expressionContext['normalized_data']);
        unset($this->expressionContext['class_metadata']);
        unset($this->expressionContext['provider']);

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

    private function getPropertyNormalizedValue($normalizedData, ClassMetadata\Property $property)
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
        ClassMetadata\Property $property
    ) : void {
        if ($property->isCollection()) {
            $modelValue = [];

            foreach ($normalizedValue as $normalizedValueItemKey => $normalizedValueItem) {
                if ($property->isObject()) {
                    $this->hydrateObjectModel($modelValueItem, $normalizedValueItem, $property);
                    $modelValue[$normalizedValueItemKey] = $modelValueItem;
                } else {
                    $this->hydrateScalarModel($modelValueItem, $normalizedValueItem, $property);
                    $modelValue[$normalizedValueItemKey] = $modelValueItem;
                }
            }
        } else {
            if ($property->isObject()) {
                $this->hydrateObjectModel($modelValue, $normalizedValue, $property);
            } else {
                $this->hydrateScalarModel($modelValue, $normalizedValue, $property);
            }
        }
    }

    private function hydrateObjectModel(&$modelValue, $normalizedValue, ClassMetadata\Property $property) : void
    {
        $propertyClass = new $property->getClass();
        $modelValue = new $propertyClass;

        $this->hydrate($modelValue, $normalizedValue);
    }

    private function hydrateScalarModel(&$modelValue, $normalizedValue, ClassMetadata\Property $property) : void
    {
        $event = $this->methodInvoker->invokeVisitorsCallbacks($property->getBeforeHydration(), new Event\BeforeHydration($normalizedValue));
        $normalizedValue = $event->getNormalizedValue();

        $modelValue = $normalizedValue;

        $event = $this->methodInvoker->invokeVisitorsCallbacks($property->getAfterHydration(), new Event\AfterHydration($modelValue, $normalizedValue));
        $modelValue = $event->getModelValue();
    }

    private function setModelValueToProperty(
        $valueToBeSetted,
        object $objectToSet,
        ClassMetadata\Property $propToSet,
        ClassMetadata $classMetadata
    ) : void {
        $this->methodInvoker->invokeVisitorsCallbacks(
            $propToSet->getBeforeSettingHydratedValue(),
            new Event\BeforeSettingHydratedValue($valueToBeSetted, $objectToSet, $propToSet->getName())
        );

        $this->memberAccessStrategyFactory->getterSetter($objectToSet, $classMetadata)->setValue($valueToBeSetted, $objectToSet, $propToSet->getName());

        $this->methodInvoker->invokeVisitorsCallbacks($propToSet->getAfterSettingHydratedValue());
    }

    private function setCollectionModelValueToProperty(
        array $valueToBeSetted,
        object $objectToSet,
        ClassMetadata\Property $propToSet,
        ClassMetadata $classMetadata
    ) : void {
        $this->methodInvoker->invokeVisitorsCallbacks(
            $propToSet->getBeforeSettingHydratedValue(),
            new Event\BeforeSettingHydratedValue($valueToBeSetted, $objectToSet, $propToSet->getName())
        );

        $this->memberAccessStrategyFactory->getterSetter($objectToSet, $classMetadata)->setValues($valueToBeSetted, $objectToSet, $propToSet->getName());

        $this->methodInvoker->invokeVisitorsCallbacks($propToSet->getAfterSettingHydratedValue());
    }

    /*public function guessMemberAccessStrategyFromLoadingOption(object $object, ClassMetadata $classMetadata, bool $byPassLoading) : MemberAccessStrategyInterface
    {
        if ($byPassLoading) {
            return $this->createPropertyAccessStrategy($object, $classMetadata);
        }

        return $this->createGetterSetterAccessStrategy($object, $classMetadata);
    }*/
}
