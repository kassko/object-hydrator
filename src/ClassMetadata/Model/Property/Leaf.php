<?php

namespace Big\Hydrator\ClassMetadata\Model\Property;

use Big\Hydrator\ClassMetadata\Model\{Property, Callbacks, DataSource, DynamicValueInterface};
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @author kko
 */
abstract class Leaf extends Property
{
    private ?string $keyInRawData = null;
    private ?string $type = null;
    private ?string $class = null;
    private bool $hydrateRawData = true;
    private ?DataSource $dataSource = null;
    private ?string $getter = null;
    private ?string $setter = null;
    private $defaultValue = null;
    private array $variables = [];
    private ?Callbacks $callbacksUsingMetadata = null;
    private ?Callbacks $callbacksFetchingData = null;
    private ?Callbacks $callbacksHydration = null;
    private ?Callbacks $callbacksAssigningHydratedValue = null;

    private ?DynamicValueInterface $discriminator = null;
    private array $dynamicAttributes = [];


    public function __construct(string $name)
    {
        parent::__construct($name);

        $this->callbacksUsingMetadata = new Callbacks;
        $this->callbacksDataFetching = new Callbacks;
        $this->callbacksHydration = new Callbacks;
        $this->callbacksAssigningHydratedValue = new Callbacks;
    }

    public function getKeyInRawData() : ?string
    {
        return $this->keyInRawData;
    }

    public function setKeyInRawData(string $keyInRawData) : self
    {
        $this->keyInRawData = $keyInRawData;

        return $this;
    }

    public function getType() : ?string
    {
        return $this->type;
    }

    public function setType(string $type) : self
    {
        $this->type = $type;

        return $this;
    }

    public function isObject() : bool
    {
        return null !== $this->class;
    }

    public function getClass() : ?string
    {
        return $this->class;
    }

    public function setClass(string $class) : self
    {
        $this->class = $class;

        return $this;
    }

    public function areRawDataToHydrate() : bool
    {
        return $this->hydrateRawData;
    }

    public function hasDataSource() : bool
    {
        return null !== $this->dataSource;
    }

    public function getDataSource() : ?DataSource
    {
        return $this->dataSource;
    }

    public function setDataSource(DataSource $dataSource) : self
    {
        $this->dataSource = $dataSource;
        return $this;
    }

    public function hasDiscriminator() : bool
    {
        return null !== $this->discriminator;
    }

    public function getDiscriminator() : ?DynamicValueInterface
    {
        return $this->discriminator;
    }

    public function setDiscriminator(DynamicValueInterface $discriminator) : self
    {
        $this->discriminator = $discriminator;

        return $this;
    }

    public function getDynamicAttributes() : array
    {
        return $this->dynamicAttributes;
    }

    public function addDynamicAttribute(DynamicValueInterface $dynamicAttributes) : self
    {
        $this->dynamicAttributes[] = $dynamicAttributes;

        return $this;
    }

    public function hasDefaultValue() : bool
    {
        return isset($this->defaultValue);
    }

    public function getDefaultValue() : ?string
    {
        return $this->defaultValue;
    }

    public function setDefaultValue(array $defaultValue) : self
    {
        $this->defaultValue = $defaultValue;

        return $this;
    }

    public function hasVariables() : bool
    {
        return count($this->variables) > 0;
    }

    public function getVariables() : array
    {
        return $this->variables;
    }

    public function setVariables(array $variables) : self
    {
        $this->variables = $variables;

        return $this;
    }

    public function hasGetter() : bool
    {
        return null !== $this->getGetter();
    }

    public function getGetter() : ?string
    {
        return $this->getter;
    }

    public function setGetter(string $getter) : self
    {
        $this->getter = $getter;

        return $this;
    }

    public function hasSetter() : bool
    {
        return null !== $this->getSetter();
    }

    public function getSetter() : ?string
    {
        return $this->setter;
    }

    public function setSetter(string $setter) : self
    {
        $this->setter = $setter;

        return $this;
    }

    public function getCallbacksUsingMetadata() : ?Callbacks
    {
        return $this->callbacksUsingMetadata;
    }

    public function setCallbacksUsingMetadata(Callbacks $callbacksUsingMetadata) : self
    {
        $this->callbacksUsingMetadata = $callbacksUsingMetadata;

        return $this;
    }

    public function getCallbacksHydration() : ?Callbacks
    {
        return $this->callbacksHydration;
    }

    public function setCallbacksHydration(Callbacks $callbacksHydration) : self
    {
        $this->callbacksHydration = $callbacksHydration;

        return $this;
    }

    public function getCallbacksAssigningHydratedValue() : ?Callbacks
    {
        return $this->callbacksAssigningHydratedValue;
    }

    public function setCallbacksAssigningHydratedValue(Callbacks $callbacksAssigningHydratedValue) : self
    {
        $this->callbacksAssigningHydratedValue = $callbacksAssigningHydratedValue;

        return $this;
    }
}
