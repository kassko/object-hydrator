<?php

namespace Kassko\ObjectHydrator\Event;

class AfterDataFetching
{
    private $normalizedValue;

    public function __construct($normalizedValue)
    {
        $this->normalizedValue = $normalizedValue;
    }

    public function getNormalizedValue()
    {
        return $this->normalizedValue;
    }

    public function updateNormalizedValue($value) : void
    {
        $this->normalizedValue = $value;
    }
}
