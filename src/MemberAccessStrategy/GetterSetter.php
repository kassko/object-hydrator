<?php

namespace Big\Hydrator\MemberAccessStrategy;

use Big\Hydrator\ClassMetadata;

use function get_class_methods;


/**
* Access logic by getters/issers/setters to object members to hydrate.
*
* @author kko
*/
class GetterSetter implements \Big\Hydrator\MemberAccessStrategyInterface
{
    private $object;
    private $classMetadata;
    private $reflectionClass;
    private $classMethods;
    private $propertyAccessStrategy;

    public function __construct(object $object, ClassMetadata $classMetadata, Property $propertyAccessStrategy)
    {
        $this->object = $object;
        $this->classMetadata = $classMetadata;
        $this->reflectionClass = $classMetadata->getReflectionClass();
        $this->classMethods = array_flip(get_class_methods($object));
        $this->propertyAccessStrategy = $propertyAccessStrategy;
    }

    public function getValue(ClassMetadata\Property $property)
    {
        $getter = $property->getGetter();
        if (isset($this->classMethods[$getter])) {
            return $this->object->$getter();
        };

        return $this->propertyAccessStrategy->getValue($property->getName());
    }

    public function setValue($value, ClassMetadata\Property $property) : void
    {
        $setter = $property->getSetter();
        if (isset($this->classMethods[$setter])) {
            $this->object->$setter($value);
        }

        $this->propertyAccessStrategy->setValue($value, $property);
    }

    public function setValues(array $collectionValue, ClassMetadata\Property $property) : void
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
