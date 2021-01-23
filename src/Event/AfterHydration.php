<?php

namespace Kassko\ObjectHydrator\Event;

class AfterHydration
{
    private $modelValue;
    private $normalizedValue;

    public function __construct($modelValue, $normalizedValue)
    {
        $this->modelValue = $modelValue;
        $this->normalizedValue = $normalizedValue;
    }

    public function getModelValue()
    {
        return $this->modelValue;
    }

    public function updateModelValue($value) : void
    {
        $this->modelValue = $value;
    }

    public function getNormalizedValue()
    {
        return $this->normalizedValue;
    }
}
