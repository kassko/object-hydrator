<?php

namespace Kassko\ObjectHydrator\Event;

class OnCustomInstantiation
{
    private $class;

    public function __construct(string $class)
    {
        $this->class = $class;
    }

    public function getClass() : string
    {
        return $this->class;
    }
}
