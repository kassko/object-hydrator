<?php

namespace Big\Hydrator\MemberAccessStrategy;

use Big\Hydrator\{ClassMetadata, CandidatePropertiesResolverAwareTrait};

use function get_class_methods;


/**
* Access logic by getters/issers/setters to object members to hydrate.
*
* @author kko
*/
class GetterSetter implements \Big\Hydrator\MemberAccessStrategyInterface
{
    use CandidatePropertiesResolverAwareTrait;

    private $propertyAccessStrategy;
    private $classMetadata;
    private $classMethods;

    public function __construct(Property $propertyAccessStrategy)
    {
        $this->propertyAccessStrategy = $propertyAccessStrategy;
    }

    public function prepare(object $object, ClassMetadata $classMetadata) : void
    {
        $this->classMetadata = $classMetadata;
        $this->classMethods = array_flip(get_class_methods($object));
    }

    public function getValue($object, $propertyName)
    {
        $getter = $this->resolveManagedProperty($propertyName, $this->classMetadata)->getGetter();
        if (isset($this->classMethods[$getter])) {
            return $object->$getter();
        };

        return $this->propertyAccessStrategy->getValue($object, $propertyName);
    }

    public function setValue($value, object $object, string $propertyName) : void
    {
        $setter = $this->resolveManagedProperty($propertyName, $this->classMetadata)->getSetter();
        if (isset($this->classMethods[$setter])) {
            $object->$setter($value);
        }

        $this->propertyAccessStrategy->setValue($value, $object, $propertyName);
    }

    public function setValues(array $collectionValue, object $object, string $propertyName) : void
    {
        $adder = $this->resolveManagedProperty($propertyName, $this->classMetadata)->getAdder();
        if (isset($adder)) {
            if ($this->resolveManagedProperty($propertyName, $this->classMetadata)->isAssocAdder()) {
                foreach ($collectionValue as $key => $item) {
                    $object->$adder($key, $item);
                }
            } else {
                foreach ($collectionValue as $item) {
                    $object->$adder($item);
                }
            }

            return;
        }

        $setter = $this->resolveManagedProperty($propertyName, $this->classMetadata)->getSetter();
        if (isset($setter)) {
            $object->$setter($collectionValue);
            return;
        }

        $this->propertyAccessStrategy->setValue($collectionValue, $object, $propertyName);
    }
}
