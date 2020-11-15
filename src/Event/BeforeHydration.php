<?php

namespace Big\Hydrator\Event;

class BeforeHydration
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

    public function setNormalizedValue($value) : void
    {
        $this->normalizedValue = $value;
    }
}
