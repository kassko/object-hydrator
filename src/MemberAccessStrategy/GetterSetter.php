<?php

namespace Kassko\ObjectHydrator\MemberAccessStrategy;

use Kassko\ObjectHydrator\ClassMetadata;

use function get_class_methods;


/**
* Access logic by getters/setters to object members to set with hydrated/model value.
*
* @author kko
*/
class GetterSetter implements \Kassko\ObjectHydrator\MemberAccessStrategyInterface
{
    private $object;
    private $reflectionClass;
    private $propertyAccessStrategy;

    public function __construct(object $object, ClassMetadata\Model\Class_ $classMetadata, Property $propertyAccessStrategy)
    {
        $this->object = $object;
        $this->reflectionClass = $classMetadata->getReflectionClass();
        $this->propertyAccessStrategy = $propertyAccessStrategy;
    }

    public function getValue(ClassMetadata\Model\Property\Leaf $property)
    {
        $getter = $property->getGetter();
        if (isset($getter) && $this->reflectionClass->hasMethod($getter)) {
            return $this->object->$getter();
        }

        return $this->propertyAccessStrategy->getValue($property);
    }

    public function setValue($value, ClassMetadata\Model\Property\Leaf $property) : void
    {
        $setter = $property->getSetter();
        //var_dump(__METHOD__, 'ICI1', $value, $setter);
        if (isset($setter) && $this->reflectionClass->hasMethod($setter)) {
            $this->object->$setter($value);
            return;
        }

        $this->propertyAccessStrategy->setValue($value, $property);
    }

    public function setValues(array $collectionValue, ClassMetadata\Model\Property\Leaf $property) : void
    {
        $adder = $property->getAdder();
        if (isset($adder)) {
            $nbParameters = count($this->reflectionClass->getMethod($adder)->getParameters());
            if ($nbParameters === 0 || $nbParameters > 2) {
                throw new \LogicException(sprintf(
                    'Cannot hydrate object "%s".' .
                    PHP_EOL . 'The adder "%s" must contains either 1 (the value to set) or 2 parameters (a key and the value to set).' .
                    PHP_EOL . 'Given "%d" parameters.',
                    $this->object,
                    $adder,
                    $nbParameters
                ));
            }

            if (2 === $nbParameters) {
                foreach ($collectionValue as $key => $item) {
                    $this->object->$adder($key, $item);
                }
            } else {//1 parameter, the value.
                foreach ($collectionValue as $item) {
                    $this->object->$adder($item);
                }
            }

            return;
        }

        $setter = $property->getSetter();
        if (isset($setter)) {
            $this->object->$setter($collectionValue);
            return;
        }

        $this->propertyAccessStrategy->setValue($collectionValue, $property);
    }
}
