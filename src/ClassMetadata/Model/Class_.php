<?php

namespace Big\Hydrator\ClassMetadata\Model;

use Big\Hydrator\{ClassMetadata, PropertyCandidatesResolver};
use Big\Hydrator\ClassMetadata\Repository\ReflectionClassRepository;

use function class_exists;

/**
 * @author kko
 */
final class Class_
{
    private array $properties = [];
    private array $basicProperties = [];
    private array $loadableProperties = [];
    private ClassMetadata\ReflectionClass $reflectionClass;
    private array $reflectionProperties = [];
    private ?Callbacks $callbacksUsingMetadata = null;
    private ?Callbacks $callbacksHydration = null;
    private ?Callbacks $callbacksAssigningHydratedValue = null;


    public function __construct(object $object, ReflectionClassRepository $reflectionClassRepository = null)
    {
        if (class_exists('Doctrine\Common\Util\ClassUtils')) {//If Doctrine is used, remove eventual proxy.
            $objectClass = \Doctrine\Common\Util\ClassUtils::getRealClass(get_class($object));
        }

        $reflectionClass = $reflectionClassRepository ? $reflectionClassRepository->findByObject($object) : null;
        $this->reflectionClass = $reflectionClass ?? new ClassMetadata\ReflectionClass(\ReflectionClass($object));

        $this->callbacksUsingMetadata = new Callbacks;
        $this->callbacksHydration = new Callbacks;
        $this->callbacksAssigningHydratedValue = new Callbacks;
    }

    public function getReflectionClass()
    {
        return $this->reflectionClass;
    }

    public function getReflectionProperties() : array
    {
        return $this->reflectionProperties;
    }

    public function setReflectionProperties() : self
    {
        $this->reflectionProperties = $reflectionProperties;

        return $this;
    }

    public function addProperty(ClassMetadata\Model\Property $property) : self
    {
        $this->properties[$property->getName()] = $property;

        /*if (false === $property->hasDataSource()) {
            $this->basicProperties[$name] = $property;
        } else {
            $this->loadableProperties[$name] = $property;
        }*/

        return $this;
    }

    public function getProperty(string $name) : ClassMetadata\Model\Property
    {
        return $this->properties[$name];
    }

    /*public function getElectedProperty(string $name, PropertyCandidatesResolver $propertyCandidatesResolver) : ClassMetadata\Model\Property
    {
        $property = $this->properties[$name];

        if ($property->hasCandidates()) {
            $property = $propertyCandidatesResolver->resolveGoodCandidates($property);
        }

        return $property;
    }*/

    public function getProperties() : array
    {
        return $this->properties;
    }

    public function getBasicProperties() : array
    {
        return $this->basicProperties;
    }

    public function getLoadableProperties() : array
    {
        return $this->loadableProperties;
    }

    public function getCallbacksUsingMetadata() : ?Callbacks
    {
        return $this->callbacksUsingMetadata;
    }

    public function addCallbackUsingMetadata(Method $callbackUsingMetadata) : self
    {
        $this->callbacksUsingMetadata->add($callbackUsingMetadata);

        return $this;
    }

    public function getCallbacksHydration() : ?Callbacks
    {
        return $this->callbacksHydration;
    }

    public function addCallbackHydration(Method $callbackHydration) : self
    {
        $this->callbacksHydration->add($callbackHydration);

        return $this;
    }

    public function getCallbacksAssigningHydratedValue() : ?Callbacks
    {
        return $this->callbacksAssigningHydratedValue;
    }

    public function addCallbackAssigningHydratedValue(Method $callbackAssigningHydratedValue) : self
    {
        $this->callbacksAssigningHydratedValue->add($callbackAssigningHydratedValue);

        return $this;
    }
}
