<?php

namespace Kassko\ObjectHydrator\Observer;

use Kassko\ObjectHydrator\Observer\Dto;
use Kassko\ObjectHydrator\MethodInvoker;
use Kassko\ObjectHydrator\Model;

class HydratorProcessingObserverManager
{
    private MethodInvoker $invoker;
    private iterable $hydratorProcessingObservers = [];


    public function __construct(MethodInvoker $methodInvoker, iterable $hydratorProcessingObservers = [])
    {
        $this->methodInvoker = $methodInvoker;
        $this->hydratorProcessingObservers = $hydratorProcessingObservers;
    }

    public function addHydratorProcessingObserver(HydratorProcessingObserver $hydratorProcessingObserver) : self
    {
        $this->hydratorProcessingObservers[] = $hydratorProcessingObserver;

        return $this;
    }

    public function classBeforeUsingMetadata(Model\Class_ $classMetadata, Dto\Class_\BeforeUsingMetadata $dto) : void
    {
        $this->methodInvoker->invokeVisitorsCallbacks(
            $classMetadata->getCallbacksUsingMetadata()->getBeforeCollection(),
            $dto
        );

        foreach ($this->hydratorProcessingObservers as $hydratorProcessingObserver) {
            $hydratorProcessingObserver->classBeforeUsingMetadata($dto);
        }
    }

    public function classAfterUsingMetadata(Model\Class_ $classMetadata, Dto\Class_\AfterUsingMetadata $dto) : void
    {
        $this->methodInvoker->invokeVisitorsCallbacks(
            $classMetadata->getCallbacksUsingMetadata()->getBeforeCollection(),
            $dto
        );

        foreach ($this->hydratorProcessingObservers as $hydratorProcessingObserver) {
            $hydratorProcessingObserver->classAfterUsingMetadata($dto);
        }
    }

    public function propertyBeforeUsingMetadata(Model\Property\Leaf $propertyMetadata, Dto\Property\BeforeUsingMetadata $dto) : void
    {
        $this->methodInvoker->invokeVisitorsCallbacks(
            $propertyMetadata->getCallbacksUsingMetadata()->getBeforeCollection(),
            $dto
        );

        foreach ($this->hydratorProcessingObservers as $hydratorProcessingObserver) {
            $hydratorProcessingObserver->propertyBeforeUsingMetadata($dto);
        }
    }

    public function propertyAfterUsingMetadata(Model\Property\Leaf $propertyMetadata, Dto\Property\AfterUsingMetadata $dto) : void
    {
        $this->methodInvoker->invokeVisitorsCallbacks(
            $propertyMetadata->getCallbacksUsingMetadata()->getAfterCollection(),
            $dto
        );

        foreach ($this->hydratorProcessingObservers as $hydratorProcessingObserver) {
            $hydratorProcessingObserver->propertyAfterUsingMetadata($dto);
        }
    }

    public function propertyBeforeHydration(Model\Property\Leaf $propertyMetadata, Dto\Property\BeforeHydration $dto) : void
    {
        $this->methodInvoker->invokeVisitorsCallbacks(
            $propertyMetadata->getCallbacksHydration()->getBeforeCollection(),
            $dto
        );

        foreach ($this->hydratorProcessingObservers as $hydratorProcessingObserver) {
            $hydratorProcessingObserver->propertyBeforeHydration($dto);
        }
    }

    public function propertyAfterHydration(Model\Property\Leaf $propertyMetadata, Dto\Property\AfterHydration $dto) : void
    {
        $this->methodInvoker->invokeVisitorsCallbacks(
            $propertyMetadata->getCallbacksHydration()->getAfterCollection(),
            $dto
        );

        foreach ($this->hydratorProcessingObservers as $hydratorProcessingObserver) {
            $hydratorProcessingObserver->propertyAfterHydration($dto);
        }
    }

    public function propertyBeforeAssigningHydratedValue(Model\Property\Leaf $propertyMetadata, Dto\Property\BeforeAssigningHydratedValue $dto) : void
    {
        $this->methodInvoker->invokeVisitorsCallbacks(
            $propertyMetadata->getCallbacksAssigningHydratedValue()->getBeforeCollection(),
            $dto
        );

        foreach ($this->hydratorProcessingObservers as $hydratorProcessingObserver) {
            $hydratorProcessingObserver->propertyBeforeAssigningHydratedValue($dto);
        }
    }

    public function propertyAfterAssigningHydratedValue(Model\Property\Leaf $propertyMetadata, Dto\Property\AfterAssigningHydratedValue $dto) : void
    {
        $this->methodInvoker->invokeVisitorsCallbacks(
            $propertyMetadata->getCallbacksAssigningHydratedValue()->getAfterCollection(),
            $dto
        );

        foreach ($this->hydratorProcessingObservers as $hydratorProcessingObserver) {
            $hydratorProcessingObserver->propertyAfterAssigningHydratedValue($dto);
        }
    }

    public function dataSourceBeforeUsingMetadata(Model\DataSource $dataSourceMetadata, Dto\DataSource\BeforeUsingMetadata $dto) : void
    {
        $this->methodInvoker->invokeVisitorsCallbacks(
            $dataSourceMetadata->getCallbacksUsingMetadata()->getBeforeCollection(),
            $dto
        );

        foreach ($this->hydratorProcessingObservers as $hydratorProcessingObserver) {
            $hydratorProcessingObserver->dataSourceBeforeUsingMetadata($dto);
        }
    }

    public function dataSourceAfterUsingMetadata(Model\DataSource $dataSourceMetadata, Dto\DataSource\AfterUsingMetadata $dto) : void
    {
        $this->methodInvoker->invokeVisitorsCallbacks(
            $dataSourceMetadata->getCallbacksUsingMetadata()->getAfterCollection(),
            $dto
        );

        foreach ($this->hydratorProcessingObservers as $hydratorProcessingObserver) {
            $hydratorProcessingObserver->dataSourceAfterUsingMetadata($dto);
        }
    }

    public function dataSourceBeforeFetchingData(Model\DataSource $dataSourceMetadata, Dto\DataSource\BeforeFetchingData $dto) : void
    {
        $this->methodInvoker->invokeVisitorsCallbacks(
            $dataSourceMetadata->getCallbacksFetchingData()->getBeforeCollection(),
            $dto
        );

        foreach ($this->hydratorProcessingObservers as $hydratorProcessingObserver) {
            $hydratorProcessingObserver->dataSourceBeforeFetchingData($dto);
        }
    }

    public function dataSourceAfterFetchingData(Model\DataSource $dataSourceMetadata, Dto\DataSource\AfterFetchingData $dto) : void
    {
        $this->methodInvoker->invokeVisitorsCallbacks(
            $dataSourceMetadata->getCallbacksFetchingData()->getAfterCollection(),
            $dto
        );

        foreach ($this->hydratorProcessingObservers as $hydratorProcessingObserver) {
            $hydratorProcessingObserver->dataSourceAfterFetchingData($dto);
        }
    }
}
