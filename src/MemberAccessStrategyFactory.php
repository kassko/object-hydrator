<?php

namespace Big\Hydrator;

use Big\Hydrator\{ClassMetadata, MemberAccessStrategy, MemberAccessStrategyInterface};

class MemberAccessStrategyFactory
{
    use PropertyMetadataVersionResolverAwareTrait;

	public function getterSetter(object $object, ClassMetadata $classMetadata) : MemberAccessStrategyInterface
    {
        $propertyAccessStrategy = $this->property($object, $classMetadata);

        $getterSetterAccessStrategy = new MemberAccessStrategy\GetterSetter(
            $propertyAccessStrategy
        )->setPropertyMetadataVersionresolver(
            $this->propertyMetadataVersionResolver
        );

        $getterSetterAccessStrategy->prepare($object, $classMetadata);

        return $getterSetterAccessStrategy;
    }

    public function property(object $object, ClassMetadata $classMetadata) : MemberAccessStrategy\Property
    {
        $propertyAccessStrategy = new MemberAccessStrategy\Property;
        $propertyAccessStrategy->prepare($object, $classMetadata);

        return $propertyAccessStrategy;
    }
}
