<?php

namespace Big\Hydrator;

use Big\Hydrator\{ClassMetadata, MemberAccessStrategy};

class MemberAccessStrategyFactory
{
	public function getterSetter(object $object, ClassMetadata $classMetadata) : MemberAccessStrategy\GetterSetter
    {
        $propertyAccessStrategy = $this->property($object, $classMetadata);

        $getterSetterAccessStrategy = (new MemberAccessStrategy\GetterSetter(
            $object,
            $classMetadata,
            $propertyAccessStrategy
        ));

        return $getterSetterAccessStrategy;
    }

    public function property(object $object, ClassMetadata $classMetadata) : MemberAccessStrategy\Property
    {
        return new MemberAccessStrategy\Property($object, $classMetadata);
    }
}
