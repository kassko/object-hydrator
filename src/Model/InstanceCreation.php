<?php

namespace Kassko\ObjectHydrator\Model;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @author kko
 */
final class InstanceCreation
{
    public ?string $factoryMethodName = null;
    public ?Method $factoryMethod = null;
    public bool $setPropertiesThroughCreationMethodWhenPossible = false;
    public bool $alwaysAccessPropertiesDirectly = false;
    public ?ArrayCollection $afterCreationMethods = null;


    public function __construct()
    {
        $this->afterCreationMethods = new ArrayCollection;
    }

    public function hasFactoryMethodName() : bool
    {
        return null !== $this->factoryMethodName;
    }

    public function getFactoryMethodName() : ?string
    {
        return $this->factoryMethodName;
    }

    public function setFactoryMethodName(string $factoryMethodName) : self
    {
        $this->factoryMethodName = $factoryMethodName;

        return $this;
    }

    public function hasFactoryMethod() : bool
    {
        return null !== $this->factoryMethod;
    }

    public function getFactoryMethod() : ?Method
    {
        return $this->factoryMethod;
    }

    public function setFactoryMethod(Method $factoryMethod) : self
    {
        $this->factoryMethod = $factoryMethod;

        return $this;
    }

    public function getSetPropertiesThroughCreationMethodWhenPossible() : bool
    {
        return $this->setPropertiesThroughCreationMethodWhenPossible;
    }

    public function setSetPropertiesThroughCreationMethodWhenPossible(bool $setPropertiesThroughCreationMethodWhenPossible) : self
    {
        $this->setPropertiesThroughCreationMethodWhenPossible = $setPropertiesThroughCreationMethodWhenPossible;

        return $this;
    }

    public function getAlwaysAccessPropertiesDirectly() : bool
    {
        return $this->alwaysAccessPropertiesDirectly;
    }

    public function setAlwaysAccessPropertiesDirectly(bool $alwaysAccessPropertiesDirectly) : self
    {
        $this->alwaysAccessPropertiesDirectly = $alwaysAccessPropertiesDirectly;

        return $this;
    }

    public function getAfterCreationMethods() : ArrayCollection
    {
        return $this->afterCreationMethods;
    }

    public function addAfterCreationMethod(Method $afterCreationMethod) : self
    {
        $this->afterCreationMethods->add($afterCreationMethod);

        return $this;
    }
}
