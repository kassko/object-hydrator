<?php

namespace Kassko\ObjectHydrator\Observer;

use Kassko\ObjectHydrator\Observer\Dto;

class HydratorProcessingObserver
{
    public function classBeforeUsingMetadata(Dto\Class_\BeforeUsingMetadata $dto) : void
    {
    }

    public function classAfterUsingMetadata(Dto\Class_\AfterUsingMetadata $dto) : void
    {
    }

    public function propertyBeforeUsingMetadata(Dto\Property\BeforeUsingMetadata $dto) : void
    {
    }

    public function propertyAfterUsingMetadata(Dto\Property\AfterUsingMetadata $dto) : void
    {
    }

    public function propertyBeforeHydration(Dto\Property\BeforeHydration $dto) : void
    {
    }

    public function propertyAfterHydration(Dto\Property\AfterHydration $dto) : void
    {
    }

    public function propertyBeforeAssigningHydratedValue(Dto\Property\BeforeAssigningHydratedValue $dto) : void
    {
    }

    public function propertyAfterAssigningHydratedValue(Dto\Property\AfterAssigningHydratedValue $dto) : void
    {
    }

    public function dataSourceBeforeUsingMetadata(Dto\DataSource\BeforeUsingMetadata $dto) : void
    {
    }

    public function dataSourceAfterUsingMetadata(Dto\DataSource\AfterUsingMetadata $dto) : void
    {
    }

    public function dataSourceBeforeFetchingData(Dto\Property\BeforeFetchingData $dto) : void
    {
    }

    public function dataSourceAfterFetchingData(Dto\Property\AfterFetchingData $dto) : void
    {
    }
}
