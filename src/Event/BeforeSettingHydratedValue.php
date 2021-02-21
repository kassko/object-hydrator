<?php

namespace Kassko\ObjectHydrator\Event;

class BeforeSettingHydratedValue
{
    private $valueToBeSetted;
    private object $objectToSet;
    private string $nameOfPropertyToSet;

    public function __construct($valueToBeSetted, object $objectToSet, string $nameOfPropertyToSet)
    {
        $this->valueToBeSetted = $valueToBeSetted;
        $this->objectToSet = $objectToSet;
        $this->nameOfPropertyToSet = $nameOfPropertyToSet;
    }

    public function getValueToBeSetted()
    {
        return $this->valueToBeSetted;
    }

    public function updateValueToBeSetted($valueToBeSetted) : void
    {
        $this->valueToBeSetted = $valueToBeSetted;
    }

    public function getObjectToSet() : object
    {
        return $this->objectToSet;
    }

    public function getNameOfPropertyToSet() : string
    {
        return $this->nameOfPropertyToSet;
    }
}
